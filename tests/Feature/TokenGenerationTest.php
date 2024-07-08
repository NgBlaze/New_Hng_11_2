<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

class TokenGenerationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function token_contains_correct_user_details()
    {
        $user = User::factory()->create([
            'firstName' => 'Santiago',
            'lastName' => 'Russel',
        ]);

        $response = $this->postJson('/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $token = $response->json('data.accessToken');

        $decodedToken = JWTAuth::setToken($token)->getPayload();
        $this->assertEquals('Santiago', $decodedToken->get('firstName'));
    }

    /** @test */
    public function token_expires_after_specified_duration()
    {
        $user = User::factory()->create([
            'firstName' => 'Santiago',
            'lastName' => 'Russel',
        ]);

        $response = $this->postJson('/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $token = $response->json('data.accessToken');

        // Wait for token expiration
        sleep(3601); // Assuming token expires in 1 hour

        $response = $this->postJson('/auth/me', [], [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'status' => 'error',
            'message' => 'Token expired',
        ]);
    }
}
