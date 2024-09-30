<?php

namespace Tests\Feature;

use Tests\TestCase;
use Laravel\Passport\Client as OauthClients;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user; 

    protected function setUp(): void
    {
        parent::setUp();

        // Create a personal access client for the tests
        $this->artisan('passport:client', [
            '--personal' => true,
            '--name' => 'Test Personal Access Client',
        ]);

        // Create a user for testing
        $this->user = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'), 
        ]);
    }

    /**
     * @group apilogintests
     */
    public function testApiLogin() 
    {
        // Generate the token for the user
        $token = $this->user->createToken('Test Personal Access Client')->accessToken;

        $body = [
            'email' => 'admin@example.com',
            'password' => 'password',
        ];

        // Check if the user exists in the database
        $this->assertDatabaseHas('users', [
            'email' => 'admin@example.com',
        ]);

        // Make the login request with the user's token
        $response = $this->json('POST', '/api/v1/login', $body, ['Authorization' => "Bearer {$token}", 'Accept' => 'application/json']);

        // Assert the response
        $response->assertStatus(200)
                 ->assertJsonStructure(['token']);
    }
}
