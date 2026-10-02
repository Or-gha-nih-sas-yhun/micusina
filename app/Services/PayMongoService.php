<?php

namespace App\Services;

use App\Models\Book;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use RuntimeException;

class PayMongoService
{
    private function client(): PendingRequest
    {
        return Http::withBasicAuth((string) config('services.paymongo.secret_key'), '')
            ->acceptJson()
            ->asJson()
            ->timeout(20);
    }

    public function createCheckout(Book $booking): array
    {
        $method = $booking->payment_method === 'GCash' ? 'gcash' : 'qrph';
        $reference = 'BK-'.str_pad((string) $booking->id, 6, '0', STR_PAD_LEFT);
        $callbackExpiresAt = now()->addDay();

        $response = $this->client()->post(config('services.paymongo.base_url').'/checkout_sessions', [
            'data' => [
                'attributes' => [
                    'billing' => [
                        'name' => $booking->name,
                        'email' => $booking->email,
                        'phone' => $booking->phone,
                    ],
                    'cancel_url' => URL::temporarySignedRoute(
                        'booking.payment.cancel',
                        $callbackExpiresAt,
                        ['booking' => $booking],
                    ),
                    'description' => 'Mi Cusina table reservation downpayment',
                    'line_items' => [[
                        'amount' => (int) round(((float) $booking->deposit_amount) * 100),
                        'currency' => 'PHP',
                        'description' => '50% table reservation downpayment',
                        'name' => $reference,
                        'quantity' => 1,
                    ]],
                    // The hosted checkout renders only payment data issued by PayMongo.
                    'payment_method_types' => ['gcash', 'qrph'],
                    'reference_number' => $reference,
                    'send_email_receipt' => true,
                    'show_description' => true,
                    'show_line_items' => true,
                    'success_url' => URL::temporarySignedRoute(
                        'booking.payment.return',
                        $callbackExpiresAt,
                        ['booking' => $booking],
                    ),
                ],
            ],
        ]);

        if ($response->failed()) {
            throw new RuntimeException($response->json('errors.0.detail', 'PayMongo could not create the checkout session.'));
        }

        return $response->json('data');
    }

    public function retrieveCheckout(string $checkoutId): array
    {
        $response = $this->client()->get(config('services.paymongo.base_url').'/checkout_sessions/'.$checkoutId);

        if ($response->failed()) {
            throw new RuntimeException('Unable to verify this PayMongo checkout session.');
        }

        return $response->json('data');
    }

    public function paidPayment(array $checkout): ?array
    {
        $payments = data_get($checkout, 'attributes.payments', []);

        foreach ($payments as $payment) {
            if (data_get($payment, 'attributes.status') === 'paid') {
                return $payment;
            }
        }

        return null;
    }

    public function validWebhookSignature(string $payload, ?string $header): bool
    {
        if (!$header || !config('services.paymongo.webhook_secret')) {
            return false;
        }

        $parts = [];
        foreach (explode(',', $header) as $part) {
            [$key, $value] = array_pad(explode('=', trim($part), 2), 2, null);
            $parts[$key] = $value;
        }

        $timestamp = $parts['t'] ?? null;
        $signatures = array_filter([$parts['te'] ?? null, $parts['li'] ?? null]);

        if (!$timestamp || !$signatures || abs(time() - (int) $timestamp) > 300) {
            return false;
        }

        $expected = hash_hmac('sha256', $timestamp.'.'.$payload, (string) config('services.paymongo.webhook_secret'));

        foreach ($signatures as $signature) {
            if (hash_equals($expected, $signature)) {
                return true;
            }
        }

        return false;
    }
}
