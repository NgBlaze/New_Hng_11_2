<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_should_register_user_successfully_with_default_organisation()
    {
        $response = $this->postJson('/auth/register', [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '1234567890'
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Registration successful',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john.doe@example.com',
        ]);
    }

    /** @test */
    public function it_should_fail_if_required_fields_are_missing()
    {
        $response = $this->postJson('/auth/register', [
            'firstName' => 'John',
            // Missing other fields
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'status' => 'error',
            'message' => 'Client error',
            'errors' => [
                'lastName' => ['The last name field is required.'],
                'email' => ['The email field is required.'],
                'password' => ['The password field is required.'],
            ],
        ]);
    }

    /** @test */
    public function it_should_fail_if_duplicate_email_exists()
    {
        User::create([
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john.doe@example.com',
            'password' => Hash::make('password123'),
            'phone' => '1234567890',
        ]);

        $response = $this->postJson('/auth/register', [
            'firstName' => 'Jane',
            'lastName' => 'Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'status' => 'error',
                'message' => 'Client error',
                'errors' => [
                    'email' => ['The email has already been taken.'],
                ],
            ]);
    }
}
