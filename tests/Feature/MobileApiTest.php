<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Food;
use App\Models\Order;
use App\Models\User;
use App\Services\RegistrationOtpSender;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Mockery\MockInterface;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Laravel\Fortify\Fortify;
use Laravel\Sanctum\Sanctum;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class MobileApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_keeps_other_device_tokens_and_returns_profile_details(): void
    {
        $user = User::factory()->create([
            'phone' => '+639171234567',
            'address' => 'Santa Fe, Cebu',
        ]);
        $previousToken = $user->createToken('mobile-app: previous device')->accessToken;
        $sameDeviceToken = $user->createToken('mobile-app: Pixel 9')->accessToken;

        $response = $this->postJson('/api/mobile/login', [
            'email' => $user->email,
            'password' => 'password',
            'device_name' => 'Pixel 9',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'user'])
            ->assertJsonPath('user.phone', '+639171234567')
            ->assertJsonPath('user.address', 'Santa Fe, Cebu');

        $this->assertDatabaseHas('personal_access_tokens', ['id' => $previousToken->id]);
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $sameDeviceToken->id]);
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'mobile-app: Pixel 9',
        ]);
    }

    public function test_login_without_a_device_name_preserves_legacy_tokens(): void
    {
        $user = User::factory()->create();
        $firstToken = $user->createToken('mobile-app')->accessToken;

        $this->postJson('/api/mobile/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertOk()->assertJsonStructure(['token', 'user']);

        $this->assertDatabaseHas('personal_access_tokens', ['id' => $firstToken->id]);
        $this->assertSame(2, $user->tokens()->where('name', 'mobile-app')->count());
    }

    public function test_login_requires_and_verifies_confirmed_two_factor_authentication(): void
    {
        $user = User::factory()->create();
        $secret = app(TwoFactorAuthenticationProvider::class)->generateSecretKey();
        $user->forceFill([
            'two_factor_secret' => Fortify::currentEncrypter()->encrypt($secret),
            'two_factor_confirmed_at' => now(),
        ])->save();
        $payload = [
            'email' => $user->email,
            'password' => 'password',
            'device_name' => 'Pixel 9',
        ];

        $this->postJson('/api/mobile/login', $payload)
            ->assertStatus(202)
            ->assertJsonPath('two_factor_required', true)
            ->assertJsonMissing(['token']);
        $this->assertDatabaseCount('personal_access_tokens', 0);

        $validCode = app(Google2FA::class)->getCurrentOtp($secret);
        $lastDigit = (int) substr($validCode, -1);
        $invalidCode = substr($validCode, 0, -1).(($lastDigit + 1) % 10);

        $this->postJson('/api/mobile/login', $payload + ['two_factor_code' => $invalidCode])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('two_factor_code');
        $this->assertDatabaseCount('personal_access_tokens', 0);

        $this->postJson('/api/mobile/login', $payload + ['two_factor_code' => $validCode])
            ->assertOk()
            ->assertJsonStructure(['token', 'user']);
        $this->assertSame(1, $user->tokens()->where('name', 'mobile-app: Pixel 9')->count());
    }

    public function test_login_has_an_independent_five_attempt_rate_limit(): void
    {
        $user = User::factory()->create();

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->getJson('/api/mobile/foods')->assertOk();
            $this->postJson('/api/mobile/login', [
                'email' => $user->email,
                'password' => 'wrong-password',
            ])->assertUnprocessable();
        }

        $this->postJson('/api/mobile/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertStatus(429);
        $this->getJson('/api/mobile/foods')->assertOk();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_cart_mutations_accept_the_legacy_string_user_id_without_weakening_ownership(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $food = $this->food();
        $cart = $this->cart($user, $food);
        $otherCart = $this->cart($otherUser, $food);

        Sanctum::actingAs($user);

        $this->patchJson('/api/mobile/cart/'.$cart->id, ['quantity' => 2])
            ->assertOk()
            ->assertJsonPath('item.quantity', '2');

        $this->deleteJson('/api/mobile/cart/'.$otherCart->id)->assertForbidden();
        $this->deleteJson('/api/mobile/cart/'.$cart->id)->assertOk();
        $this->assertDatabaseMissing('carts', ['id' => $cart->id]);
    }

    public function test_orders_expose_the_existing_six_digit_public_order_number(): void
    {
        $user = User::factory()->create();
        $order = $this->order(['user_id' => $user->id, 'email' => $user->email]);

        Sanctum::actingAs($user);

        $this->getJson('/api/mobile/orders')
            ->assertOk()
            ->assertJsonPath('orders.0.order_number', str_pad((string) $order->id, 6, '0', STR_PAD_LEFT));
    }

    public function test_non_cash_checkout_requires_a_payment_reference(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/mobile/checkout', $this->checkoutPayload([
            'payment_method' => 'GCash',
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('payment_reference');
    }

    public function test_checkout_uses_the_current_locked_food_price_and_allows_cod_without_a_reference(): void
    {
        $user = User::factory()->create();
        $food = $this->food(['price' => '125.50', 'stock' => 20]);
        $cart = $this->cart($user, $food, ['quantity' => 2, 'price' => '2.00']);

        Sanctum::actingAs($user);

        $this->postJson('/api/mobile/checkout', $this->checkoutPayload())
            ->assertCreated()
            ->assertJsonPath('message', 'Order placed.');

        $this->assertDatabaseHas('orders', [
            'email' => $user->email,
            'quantity' => '2',
            'price' => '251',
            'payment_reference' => null,
        ]);
        $this->assertDatabaseMissing('carts', ['id' => $cart->id]);
        $this->assertSame(18, (int) $food->fresh()->stock);
    }

    public function test_foods_include_an_absolute_image_url_and_keep_the_filename(): void
    {
        $this->food(['image' => 'meal.jpg']);

        $this->getJson('/api/mobile/foods')
            ->assertOk()
            ->assertJsonPath('foods.0.image', 'meal.jpg')
            ->assertJsonPath('foods.0.image_url', asset('food_img/meal.jpg'));
    }

    public function test_known_dishes_use_the_same_menu_photo_as_the_website(): void
    {
        $this->food(['title' => '2-Pc Chicken Meal', 'image' => '1779593449.png']);

        $this->getJson('/api/mobile/foods')
            ->assertOk()
            ->assertJsonPath('foods.0.image', '1779593449.png')
            ->assertJsonPath('foods.0.image_url', asset('assets/imgs/2-pcs-chicken-meal-pink-v8.png'));
    }

    public function test_cart_items_include_an_absolute_image_url(): void
    {
        $user = User::factory()->create();
        $burger = $this->food(['title' => 'Chicken Burger', 'image' => 'burger.png']);
        $meal = $this->food(['image' => 'meal.jpg']);
        $this->cart($user, $burger);
        Sanctum::actingAs($user);

        $this->getJson('/api/mobile/cart')
            ->assertOk()
            ->assertJsonPath('items.0.image', 'burger.png')
            ->assertJsonPath('items.0.image_url', asset('assets/imgs/chicken-burger-pink-v8.png'));

        $this->postJson("/api/mobile/cart/{$meal->id}", ['quantity' => 1])
            ->assertOk()
            ->assertJsonPath('item.image_url', asset('food_img/meal.jpg'));
    }

    public function test_foods_include_the_menu_category_for_the_mobile_filters(): void
    {
        $this->food(['category' => 'Rice meals']);

        $this->getJson('/api/mobile/foods')
            ->assertOk()
            ->assertJsonPath('foods.0.category', 'Rice meals');
    }

    public function test_mobile_registration_sends_the_existing_email_otp_before_creating_a_user(): void
    {
        $this->mock(RegistrationOtpSender::class, function (MockInterface $mock) {
            $mock->shouldReceive('sendEmail')->once()->andReturnTrue();
        });

        $response = $this->postJson('/api/mobile/register/send-verification', [
            'name' => 'Mobile Customer',
            'email' => 'mobile@example.com',
            'phone' => '09171234567',
            'address' => 'Santa Fe, Cebu',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertStatus(202)->assertJsonStructure(['registration_id', 'expires_in']);
        $this->assertDatabaseMissing('users', ['email' => 'mobile@example.com']);
        $pending = Cache::get('mobile-registration:'.$response->json('registration_id'));
        $this->assertSame('+639171234567', $pending['phone']);
        $this->assertTrue(Hash::check('secret123', $pending['password']));
    }

    public function test_mobile_registration_verifies_otp_then_creates_a_customer_and_token(): void
    {
        $registrationId = (string) Str::uuid();
        Cache::put('mobile-registration:'.$registrationId, [
            'name' => 'Mobile Customer', 'email' => 'mobile@example.com', 'phone' => '+639171234567',
            'address' => 'Santa Fe, Cebu', 'password' => Hash::make('secret123'), 'email_code' => Hash::make('123456'),
        ], now()->addMinutes(10));

        $this->postJson('/api/mobile/register/verify', [
            'registration_id' => $registrationId, 'email_code' => '123456', 'device_name' => 'Pixel 9',
        ])->assertCreated()->assertJsonStructure(['token', 'user'])->assertJsonPath('user.email', 'mobile@example.com');

        $this->assertDatabaseHas('users', ['email' => 'mobile@example.com', 'phone' => '+639171234567']);
        $this->assertNotNull(User::where('email', 'mobile@example.com')->firstOrFail()->email_verified_at);
        $this->assertNull(Cache::get('mobile-registration:'.$registrationId));
    }

    public function test_riders_only_receive_their_assigned_orders(): void
    {
        $rider = User::factory()->create(['usertype' => 'staff', 'staff_role' => 'rider']);
        $otherRider = User::factory()->create(['usertype' => 'staff', 'staff_role' => 'rider']);
        $assigned = $this->order(['rider_id' => $rider->id, 'delivery_status' => 'On The Way']);
        $this->order(['rider_id' => $otherRider->id, 'delivery_status' => 'On The Way']);

        Sanctum::actingAs($rider);

        $this->getJson('/api/mobile/staff/orders')
            ->assertOk()
            ->assertJsonCount(1, 'orders')
            ->assertJsonPath('orders.0.id', $assigned->id);
    }

    public function test_delivery_status_updates_every_item_and_releases_the_rider(): void
    {
        $cashier = User::factory()->create(['usertype' => 'staff', 'staff_role' => 'cashier']);
        $rider = User::factory()->create(['usertype' => 'staff', 'staff_role' => 'rider', 'rider_available' => false]);
        $groupId = (string) Str::uuid();
        $first = $this->order(['checkout_group_id' => $groupId, 'email' => 'group@example.com', 'rider_id' => $rider->id, 'delivery_status' => 'On The Way', 'payment_status' => 'Paid']);
        $second = $this->order(['checkout_group_id' => $groupId, 'email' => 'group@example.com', 'rider_id' => $rider->id, 'delivery_status' => 'On The Way', 'payment_status' => 'Paid']);
        $otherCheckout = $this->order(['checkout_group_id' => (string) Str::uuid(), 'email' => 'group@example.com']);

        Sanctum::actingAs($cashier);

        $this->patchJson('/api/mobile/staff/orders/'.$first->id, ['delivery_status' => 'Delivered'])
            ->assertOk()
            ->assertJsonPath('order.delivery_status', 'Delivered');

        $this->assertSame('Delivered', $first->fresh()->delivery_status);
        $this->assertSame('Delivered', $second->fresh()->delivery_status);
        $this->assertSame('In Progress', $otherCheckout->fresh()->delivery_status);
        $this->assertTrue((bool) $rider->fresh()->rider_available);
    }

    public function test_legacy_delivery_updates_only_that_row_and_keeps_busy_riders_unavailable(): void
    {
        $rider = User::factory()->create(['usertype' => 'staff', 'staff_role' => 'rider', 'rider_available' => false]);
        $first = $this->order(['rider_id' => $rider->id, 'delivery_status' => 'On The Way', 'payment_status' => 'Paid']);
        $second = $this->order(['rider_id' => $rider->id, 'delivery_status' => 'On The Way', 'payment_status' => 'Paid']);

        Sanctum::actingAs($rider);

        $this->patchJson('/api/mobile/staff/orders/'.$first->id, ['delivery_status' => 'Delivered'])
            ->assertOk()
            ->assertJsonPath('order.delivery_status', 'Delivered');

        $this->assertSame('On The Way', $second->fresh()->delivery_status);
        $this->assertFalse($rider->fresh()->rider_available);

        $this->patchJson('/api/mobile/staff/orders/'.$second->id, ['delivery_status' => 'Delivered'])->assertOk();
        $this->assertTrue($rider->fresh()->rider_available);
    }

    public function test_staff_cannot_mark_an_unpaid_order_delivered(): void
    {
        $rider = User::factory()->create(['usertype' => 'staff', 'staff_role' => 'rider']);
        $order = $this->order(['rider_id' => $rider->id, 'delivery_status' => 'On The Way', 'payment_status' => 'Unpaid']);
        Sanctum::actingAs($rider);

        $this->patchJson('/api/mobile/staff/orders/'.$order->id, ['delivery_status' => 'Delivered'])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Payment must be marked as paid before an order can be delivered.');

        $this->assertSame('On The Way', $order->fresh()->delivery_status);
    }

    public function test_reservations_reject_a_time_that_has_already_passed_in_manila(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-10 04:00:00', 'UTC'));
        Sanctum::actingAs(User::factory()->create());

        try {
            $this->postJson('/api/mobile/reservations', [
                'first_name' => 'Mobile',
                'last_name' => 'Customer',
                'phone' => '09171234567',
                'guest' => 2,
                'date' => '2026-09-10',
                'time' => '11:59 AM',
                'payment_method' => 'GCash',
            ])->assertUnprocessable()->assertJsonPath('message', 'Choose a future reservation time.');
        } finally {
            Carbon::setTestNow();
        }
    }

    private function food(array $attributes = []): Food
    {
        $food = new Food;
        $food->title = $attributes['title'] ?? 'Test Meal';
        $food->detail = $attributes['detail'] ?? 'Freshly prepared';
        $food->category = $attributes['category'] ?? 'All menu';
        $food->price = $attributes['price'] ?? '100';
        $food->stock = $attributes['stock'] ?? 10;
        $food->image = $attributes['image'] ?? 'test-meal.jpg';
        $food->save();

        return $food;
    }

    private function cart(User $user, Food $food, array $attributes = []): Cart
    {
        return Cart::create([
            'userid' => (string) $user->id,
            'food_id' => $food->id,
            'title' => $food->title,
            'details' => $food->detail,
            'price' => $attributes['price'] ?? '100',
            'quantity' => $attributes['quantity'] ?? 1,
            'image' => $food->image,
        ]);
    }

    private function checkoutPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Mobile Customer',
            'phone' => '09171234567',
            'municipality' => 'Santa Fe',
            'barangay' => 'Poblacion',
            'purok' => '1',
            'payment_method' => 'Cash on Delivery',
        ], $overrides);
    }

    private function order(array $attributes = []): Order
    {
        return Order::create(array_merge([
            'name' => 'Mobile Customer',
            'email' => 'customer@example.com',
            'phone' => '09171234567',
            'address' => 'Poblacion, Santa Fe, Cebu',
            'title' => 'Test Meal',
            'quantity' => 1,
            'price' => 100,
            'image' => 'test-meal.jpg',
            'delivery_status' => 'In Progress',
            'payment_method' => 'Cash on Delivery',
            'payment_status' => 'Unpaid',
        ], $attributes));
    }
}
