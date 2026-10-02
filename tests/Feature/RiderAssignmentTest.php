<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RiderAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_can_assign_only_an_available_rider_and_the_rider_is_notified(): void
    {
        $cashier = User::factory()->create(['usertype' => 'staff', 'staff_role' => 'cashier']);
        $rider = User::factory()->create(['usertype' => 'staff', 'staff_role' => 'rider', 'rider_available' => true]);
        $order = $this->order();

        $this->actingAs($cashier)
            ->post('/assign_rider/'.$order->id, ['rider_id' => $rider->id])
            ->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'rider_id' => $rider->id,
            'delivery_status' => 'On The Way',
            'confirmed_by' => $cashier->id,
        ]);
        $this->assertFalse($rider->fresh()->rider_available);
        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $rider->id,
        ]);
    }

    public function test_assignment_rejects_unavailable_or_non_rider_accounts(): void
    {
        $cashier = User::factory()->create(['usertype' => 'staff', 'staff_role' => 'cashier']);
        $unavailableRider = User::factory()->create(['usertype' => 'staff', 'staff_role' => 'rider', 'rider_available' => false]);
        $customer = User::factory()->create();

        $this->actingAs($cashier)
            ->post('/assign_rider/'.$this->order()->id, ['rider_id' => $unavailableRider->id])
            ->assertUnprocessable();
        $this->actingAs($cashier)
            ->post('/assign_rider/'.$this->order(['email' => 'another@example.com'])->id, ['rider_id' => $customer->id])
            ->assertNotFound();
    }

    public function test_non_rider_staff_can_assign_an_available_rider(): void
    {
        $staff = User::factory()->create(['usertype' => 'staff', 'staff_role' => null]);
        $rider = User::factory()->create(['usertype' => 'staff', 'staff_role' => 'rider', 'rider_available' => true]);

        $this->actingAs($staff)
            ->post('/assign_rider/'.$this->order()->id, ['rider_id' => $rider->id])
            ->assertRedirect();
    }

    public function test_an_unpaid_order_cannot_be_marked_delivered(): void
    {
        $cashier = User::factory()->create(['usertype' => 'staff', 'staff_role' => 'cashier']);
        $rider = User::factory()->create(['usertype' => 'staff', 'staff_role' => 'rider']);
        $order = $this->order(['rider_id' => $rider->id, 'delivery_status' => 'On The Way', 'payment_status' => 'Unpaid']);

        $this->actingAs($cashier)->post('/delivered/'.$order->id)->assertUnprocessable();
        $this->assertSame('On The Way', $order->fresh()->delivery_status);
    }

    private function order(array $attributes = []): Order
    {
        return Order::create(array_merge([
            'name' => 'Delivery Customer',
            'email' => 'delivery@example.com',
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
