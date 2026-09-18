<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Sign In');
    }

    public function test_register_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
        $response->assertSee('Create Account');
    }

    public function test_user_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        $user = User::where('email', 'john@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals(5, $user->invoice_credits); // 5 free starter credits
    }

    public function test_user_can_login(): void
    {
        $user = User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'invoice_credits' => 10,
        ]);

        $response = $this->post('/login', [
            'email' => 'jane@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_use_demo_login(): void
    {
        $response = $this->get('/auth/demo-login/user');
        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
    }

    public function test_regular_user_cannot_access_admin(): void
    {
        $user = User::create([
            'name' => 'Standard User',
            'email' => 'standard@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'invoice_credits' => 5,
        ]);

        $response = $this->actingAs($user)->get('/admin');
        $response->assertStatus(403);
    }

    public function test_admin_can_access_admin(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'invoice_credits' => 9999,
        ]);

        $response = $this->actingAs($admin)->get('/admin');
        $response->assertStatus(200);
        $response->assertSee('Platform Metrics');
    }
}
