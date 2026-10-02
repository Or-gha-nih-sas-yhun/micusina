<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_responses_cannot_be_embedded_by_another_site(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Cross-Origin-Opener-Policy', 'same-origin')
            ->assertHeader('Cross-Origin-Resource-Policy', 'same-origin');
    }

    public function test_authenticated_responses_are_not_cached(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/my_orders')
            ->assertOk()
            ->assertHeader('Cache-Control', 'max-age=0, no-store, private');
    }
}
