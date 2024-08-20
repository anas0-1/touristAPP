<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_register_a_user()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['token']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_login_a_user()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['token']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_logout_a_user()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->postJson('/api/logout');

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Logged out successfully']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_send_reset_link_email()
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $response = $this->postJson('/api/password/email', [
            'email' => $user->email,
        ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Password reset link sent to your email address.']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_reset_password()
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $token = \Illuminate\Support\Facades\Password::createToken($user);

        $response = $this->postJson('/api/password/reset', [
            'email' => $user->email,
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
            'token' => $token,
        ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Password has been reset successfully.']);
    }
}
