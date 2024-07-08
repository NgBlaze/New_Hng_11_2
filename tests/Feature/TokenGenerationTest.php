<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class TokenGenerationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function token_contains_correct_user_details()
    {
        // Arrange
        $user = User::factory()->create([
            'password' => Hash::make('password123')
        ]);

        // Act
        $response = $this->postJson('/auth/login', [
            'email' => $user->email,
            'password' => 'password123'
        ]);

        $token = $response->json('data.accessToken');

        // Decode the token
        $decoded = JWTAuth::setToken($token)->getPayload()->toArray();

        // Assert
        $this->assertEquals($user->id, $decoded['sub']);
    }

    /** @test */
    public function token_expires_after_specified_duration()
    {
        // Arrange
        $user = User::factory()->create([
            'password' => Hash::make('password123')
        ]);

        $response = $this->postJson('/auth/login', [
            'email' => $user->email,
            'password' => 'password123'
        ]);

        $token = $response->json('data.accessToken');

        // Wait for token to expire
        sleep(3600); // Adjust based on token expiry time in seconds

        // Act & Assert
        $response = $this->get('/api/user', [
            'Authorization' => "Bearer $token"
        ]);

        $response->assertStatus(401);
    }
}
