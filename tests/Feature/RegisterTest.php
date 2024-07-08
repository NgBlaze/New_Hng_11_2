<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use App\Models\User;
use App\Models\Organisation;

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
            'phone' => '1234567890'
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Registration successful',
            ]);

        $responseData = $response->json('data');

        $this->assertNotNull($responseData['accessToken']);
        $this->assertEquals('John', $responseData['user']['firstName']);

        $organisation = Organisation::where('name', "John's Organisation")->first();
        $this->assertNotNull($organisation);
    }

    /** @test */
    public function it_should_fail_if_required_fields_are_missing()
    {
        $response = $this->postJson('/auth/register', [
            'firstName' => 'John',
            'email' => 'john.doe@example.com',
            // Missing lastName and password
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'status' => 'Bad request',
                'message' => 'Registration unsuccessful',
            ]);
    }

    /** @test */
    public function it_should_fail_if_duplicate_email_exists()
    {
        $this->postJson('/auth/register', [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password123',
            'phone' => '1234567890'
        ]);

        $response = $this->postJson('/auth/register', [
            'firstName' => 'Jane',
            'lastName' => 'Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password123',
            'phone' => '0987654321'
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'status' => 'Bad request',
                'message' => 'Registration unsuccessful',
            ]);
    }
}
