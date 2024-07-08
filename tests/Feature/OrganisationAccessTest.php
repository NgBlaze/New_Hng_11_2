<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use App\Models\User;
use App\Models\Organisation;

class OrganisationAccessTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_cannot_access_organisations_they_do_not_belong_to()
    {
        // Arrange
        $user1 = User::factory()->create([
            'password' => Hash::make('password123')
        ]);

        $user2 = User::factory()->create([
            'password' => Hash::make('password123')
        ]);

        $organisation = Organisation::create([
            'name' => "User1's Organisation",
            'description' => "An organisation for User1"
        ]);

        $organisation->users()->attach($user1->id);

        // Act
        $response = $this->postJson('/auth/login', [
            'email' => $user2->email,
            'password' => 'password123'
        ]);

        $token = $response->json('data.accessToken');

        $response = $this->get('/api/organisations/' . $organisation->id, [
            'Authorization' => "Bearer $token"
        ]);

        // Assert
        $response->assertStatus(403);
    }
}
