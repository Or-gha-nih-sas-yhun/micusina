<?php

namespace Tests\Feature;

use App\Models\Food;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function food(): Food
    {
        $food = new Food;
        $food->forceFill(['title' => 'Guest Meal', 'detail' => 'Fresh meal', 'price' => '100', 'stock' => 5, 'image' => 'meal.jpg'])->save();
        return $food;
    }

    public function test_guest_can_checkout_and_receive_a_private_receipt_without_creating_an_account(): void
    {
        $food = $this->food();
        $this->post('/add_cart/'.$food->id, ['qty' => 2])->assertRedirect();
        $this->get('/checkout')->assertOk()->assertSeeText('Guest checkout')
            ->assertSee('name="address"', false)->assertDontSee('name="email"', false)->assertDontSee('name="name"', false);
        $this->post('/confirm_order', ['phone' => '09171234567', 'address' => 'Purok 1, Poblacion, Santa Fe', 'payment_method' => 'GCash'])
            ->assertRedirect(route('guest.receipt'))->assertSessionMissing('guest_cart');
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseHas('orders', ['name' => 'Guest', 'phone' => '09171234567', 'quantity' => 2, 'price' => 200, 'payment_method' => 'Cash on Delivery', 'payment_status' => 'Unpaid']);
        $this->assertSame(3, (int) $food->fresh()->stock);
        $this->get('/guest/receipt')->assertOk()->assertSeeText('Guest Meal')->assertSeeText('Back to Menu')->assertDontSeeText('Track Order');
        $this->post('/confirm_order', ['phone' => '09171234567', 'address' => 'Poblacion'])->assertRedirect('/my_cart');
        $this->assertDatabaseCount('orders', 1);
        $this->flushSession();
        $this->get('/guest/receipt')->assertNotFound();
    }

    public function test_guest_checkout_validates_details_and_keeps_cart_on_failure(): void
    {
        $food = $this->food();
        $this->withSession(['guest_cart' => [$food->id => ['quantity' => 2]]])
            ->post('/confirm_order', ['phone' => '123', 'address' => '   '])
            ->assertSessionHasErrors(['phone', 'address'])->assertSessionHas('guest_cart');
        $this->assertDatabaseCount('orders', 0);
        $this->get('/checkout')->assertOk()->assertSeeText('Enter only your phone number');
    }

    public function test_unavailable_item_rolls_back_the_entire_guest_order(): void
    {
        $first = $this->food();
        $second = $this->food();
        $second->forceFill(['stock' => 0])->save();
        $this->withSession(['guest_cart' => [$first->id => ['quantity' => 1], $second->id => ['quantity' => 1]]])
            ->post('/confirm_order', ['phone' => '09171234567', 'address' => 'Poblacion, Santa Fe'])
            ->assertSessionHasErrors('cart')->assertSessionHas('guest_cart');
        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(5, (int) $first->fresh()->stock);
    }
}
