<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiPrototypeTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_endpoint_creates_business_and_user(): void
    {
        $payload = [
            'name' => 'Demo Owner',
            'email' => 'demo@example.com',
            'password' => 'password123',
            'business_name' => 'Demo Shop',
            'role' => 'owner',
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email'],
                'token',
            ]);
    }
}
