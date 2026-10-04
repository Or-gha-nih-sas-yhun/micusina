<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartHeadingTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_page_keeps_cart_content_without_the_redundant_page_heading(): void
    {
        $customer = User::factory()->create(['usertype' => 'user']);

        $this->actingAs($customer)->get('/my_cart')
            ->assertOk()
            ->assertDontSeeText('Cart Page')
            ->assertSeeText('Shopping Cart')
            ->assertSeeText('Order Summary')
            ->assertSeeText('Continue Shopping')
            ->assertSee('href="' . url('/?section=food') . '"', false);
    }

    public function test_embedded_cart_keeps_cart_content_without_the_redundant_page_heading(): void
    {
        $customer = User::factory()->create(['usertype' => 'user']);

        $this->actingAs($customer)->get('/my_cart?embed=1')
            ->assertOk()
            ->assertDontSeeText('Cart Page')
            ->assertSeeText('Shopping Cart')
            ->assertSeeText('Order Summary')
            ->assertDontSeeText('Continue Shopping');
    }
}
