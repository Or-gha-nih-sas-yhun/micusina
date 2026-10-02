<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use App\Services\PayMongoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Illuminate\Testing\TestResponse;
use RuntimeException;
use Tests\TestCase;

class PayMongoCallbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_uses_temporary_signed_return_urls(): void
    {
        config([
            'services.paymongo.base_url' => 'https://api.paymongo.test/v1',
            'services.paymongo.secret_key' => 'sk_test_example',
        ]);
        Http::fake([
            'https://api.paymongo.test/v1/checkout_sessions' => Http::response([
                'data' => ['id' => 'cs_test_signed_callbacks'],
            ], 201),
        ]);
        $booking = $this->booking();

        app(PayMongoService::class)->createCheckout($booking);

        $recorded = Http::recorded()->first();
        $this->assertNotNull($recorded);
        $attributes = $recorded[0]->data()['data']['attributes'];
        $successUrl = $attributes['success_url'];
        $cancelUrl = $attributes['cancel_url'];

        $this->assertTrue(URL::hasValidSignature(Request::create($successUrl)));
        $this->assertTrue(URL::hasValidSignature(Request::create($cancelUrl)));

        parse_str((string) parse_url($successUrl, PHP_URL_QUERY), $successQuery);
        parse_str((string) parse_url($cancelUrl, PHP_URL_QUERY), $cancelQuery);
        $this->assertSame($successQuery['expires'], $cancelQuery['expires']);
        $this->assertGreaterThan(now()->timestamp, (int) $successQuery['expires']);
        $this->assertLessThanOrEqual(now()->addDay()->timestamp, (int) $successQuery['expires']);
    }

    public function test_web_booking_stays_on_the_booking_page_for_gcash_verification(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/book_table', [
            'first_name' => 'Web',
            'last_name' => 'Customer',
            'email' => $user->email,
            'phone' => '09171234567',
            'n_guest' => 2,
            'date' => now()->addDays(2)->toDateString(),
            'time' => '6:00 PM',
            'payment_method' => 'GCash',
            'payment_reference' => 'GCASH-123456',
        ]);

        $response->assertRedirect('/?section=book')
            ->assertSessionHas('booking_receipt.payment_status', 'Pending Verification');
        $this->assertDatabaseHas('books', [
            'user_id' => $user->id,
            'payment_method' => 'GCash',
            'gcash_transaction_reference' => 'GCASH-123456',
            'payment_status' => 'Pending Verification',
            'status' => 'Pending',
        ]);
    }

    public function test_booking_page_shows_only_the_gcash_payment_popup(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/?section=book')
            ->assertOk()
            ->assertSeeText('Complete Your GCash Payment')
            ->assertSeeText('GCash transaction reference')
            ->assertDontSeeText('PayMongo')
            ->assertSee('form="bookTableForm"', false);
    }

    public function test_staff_can_verify_a_pending_gcash_payment_before_approval(): void
    {
        $booking = $this->booking([
            'gcash_transaction_reference' => 'GCASH-654321',
            'payment_status' => 'Pending Verification',
            'status' => 'Pending',
        ]);
        $staff = User::factory()->create(['usertype' => 'staff']);

        $this->actingAs($staff)->get('/reservations')
            ->assertOk()
            ->assertSeeText('GCASH-654321')
            ->assertSeeText('Verify Payment');

        $this->actingAs($staff)
            ->post('/verify_reservation_payment/'.$booking->id)
            ->assertRedirect();

        $this->assertDatabaseHas('books', [
            'id' => $booking->id,
            'payment_status' => 'Paid',
        ]);

        $this->actingAs($staff)
            ->post('/approve_reservation/'.$booking->id)
            ->assertRedirect();

        $this->assertDatabaseHas('books', [
            'id' => $booking->id,
            'status' => 'Approved',
        ]);
    }

    public function test_guest_can_complete_payment_with_a_valid_signed_url(): void
    {
        $booking = $this->booking(['paymongo_checkout_id' => 'cs_test_paid']);
        $payMongo = $this->mock(PayMongoService::class);
        $payMongo->shouldReceive('retrieveCheckout')
            ->once()
            ->with('cs_test_paid')
            ->andReturn(['id' => 'cs_test_paid']);
        $payMongo->shouldReceive('paidPayment')
            ->once()
            ->andReturn([
                'id' => 'pay_test_paid',
                'attributes' => [
                    'status' => 'paid',
                    'amount' => 12500,
                    'currency' => 'PHP',
                ],
            ]);

        $response = $this->get($this->signedUrl('booking.payment.return', $booking));

        $this->assertGuest();
        $response->assertRedirect('/?section=book')
            ->assertSessionHas('message')
            ->assertSessionHas('booking_receipt');
        $this->assertDatabaseHas('books', [
            'id' => $booking->id,
            'payment_status' => 'Paid',
            'paymongo_payment_id' => 'pay_test_paid',
            'status' => 'Pending',
        ]);
    }

    public function test_guest_can_follow_a_valid_signed_cancel_url(): void
    {
        $booking = $this->booking();

        $response = $this->get($this->signedUrl('booking.payment.cancel', $booking));

        $this->assertGuest();
        $response->assertRedirect('/?section=book')
            ->assertSessionHasErrors([
                'payment' => 'Payment was cancelled. Your booking has not been confirmed.',
            ]);
    }

    public function test_unsigned_expired_and_tampered_callback_urls_are_rejected(): void
    {
        $booking = $this->booking();
        $otherBooking = $this->booking();

        $this->get(route('booking.payment.return', $booking))->assertForbidden();
        $this->get(URL::temporarySignedRoute(
            'booking.payment.return',
            now()->subMinute(),
            ['booking' => $booking],
        ))->assertForbidden();

        $validUrl = $this->signedUrl('booking.payment.return', $booking);
        $tamperedUrl = preg_replace(
            '#/booking/payment/return/'.$booking->id.'(?=\?)#',
            '/booking/payment/return/'.$otherBooking->id,
            $validUrl,
        );
        $this->assertIsString($tamperedUrl);
        $this->get($tamperedUrl)->assertForbidden();
    }

    public function test_verification_outage_returns_a_safe_browser_fallback(): void
    {
        $booking = $this->booking(['paymongo_checkout_id' => 'cs_test_unavailable']);
        $payMongo = $this->mock(PayMongoService::class);
        $payMongo->shouldReceive('retrieveCheckout')
            ->once()
            ->andThrow(new RuntimeException('Provider unavailable'));

        $response = $this->get($this->signedUrl('booking.payment.return', $booking));

        $response->assertRedirect('/?section=book')
            ->assertSessionHasErrors([
                'payment' => 'We could not verify the payment right now. Your reservation will update automatically once PayMongo confirms it.',
            ]);
        $this->assertDatabaseHas('books', [
            'id' => $booking->id,
            'payment_status' => 'Pending',
            'status' => 'Awaiting Payment',
        ]);
    }

    public function test_checkout_webhook_confirms_the_embedded_payment_without_a_browser_return(): void
    {
        $booking = $this->booking(['paymongo_checkout_id' => 'cs_webhook_paid']);

        $this->postSignedWebhook('checkout_session.payment.paid', [
            'id' => 'cs_webhook_paid',
            'type' => 'checkout_session',
            'attributes' => [
                'reference_number' => 'BK-'.str_pad((string) $booking->id, 6, '0', STR_PAD_LEFT),
                'payments' => [$this->payment()],
            ],
        ])->assertOk();

        $this->assertDatabaseHas('books', [
            'id' => $booking->id,
            'payment_status' => 'Paid',
            'paymongo_payment_id' => 'pay_webhook_paid',
            'status' => 'Pending',
        ]);
    }

    public function test_repeated_payment_webhooks_preserve_approval_and_the_original_paid_time(): void
    {
        $booking = $this->booking(['paymongo_checkout_id' => 'cs_webhook_paid']);
        $payment = $this->payment();
        $payment['attributes']['checkout_session_id'] = 'cs_webhook_paid';

        $this->postSignedWebhook('payment.paid', $payment)->assertOk();
        $paidAt = $booking->fresh()->paid_at;
        $booking->update(['status' => 'Approved']);

        $this->travel(1)->minutes();
        $this->postSignedWebhook('payment.paid', $payment)->assertOk();

        $this->assertSame('Approved', $booking->fresh()->status);
        $this->assertTrue($booking->fresh()->paid_at->equalTo($paidAt));
    }

    public function test_webhooks_cannot_confirm_the_wrong_amount_or_an_invalid_signature(): void
    {
        $booking = $this->booking(['paymongo_checkout_id' => 'cs_webhook_paid']);
        $payment = $this->payment();
        $payment['attributes']['checkout_session_id'] = 'cs_webhook_paid';
        $payment['attributes']['amount'] = 1;

        $this->postSignedWebhook('payment.paid', $payment)->assertOk();
        $this->postJson('/paymongo/webhook', [
            'data' => ['attributes' => ['type' => 'payment.paid', 'data' => $this->payment()]],
        ])->assertUnauthorized();

        $this->assertDatabaseHas('books', [
            'id' => $booking->id,
            'payment_status' => 'Pending',
            'status' => 'Awaiting Payment',
        ]);
    }

    private function payment(): array
    {
        return [
            'id' => 'pay_webhook_paid',
            'type' => 'payment',
            'attributes' => [
                'status' => 'paid',
                'amount' => 12500,
                'currency' => 'PHP',
            ],
        ];
    }

    private function postSignedWebhook(string $eventType, array $resource): TestResponse
    {
        config(['services.paymongo.webhook_secret' => 'webhook-test-secret']);
        $payload = json_encode([
            'data' => ['attributes' => ['type' => $eventType, 'data' => $resource]],
        ], JSON_THROW_ON_ERROR);
        $timestamp = time();
        $signature = hash_hmac('sha256', $timestamp.'.'.$payload, 'webhook-test-secret');

        return $this->call('POST', '/paymongo/webhook', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_PAYMONGO_SIGNATURE' => 't='.$timestamp.',te='.$signature,
        ], $payload);
    }

    private function signedUrl(string $route, Book $booking): string
    {
        return URL::temporarySignedRoute($route, now()->addMinutes(10), ['booking' => $booking]);
    }

    private function booking(array $overrides = []): Book
    {
        $user = User::factory()->create();

        return Book::create(array_merge([
            'user_id' => $user->id,
            'first_name' => 'Mobile',
            'last_name' => 'Customer',
            'name' => 'Mobile Customer',
            'email' => $user->email,
            'phone' => '09171234567',
            'guest' => 2,
            'date' => now()->addDays(2)->toDateString(),
            'time' => '6:00 PM',
            'reservation_price' => 250,
            'deposit_amount' => 125,
            'payment_method' => 'GCash',
            'gcash_reference' => 'BK-TEST',
            'payment_status' => 'Pending',
            'status' => 'Awaiting Payment',
        ], $overrides));
    }
}
