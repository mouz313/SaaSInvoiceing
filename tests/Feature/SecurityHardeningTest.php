<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use App\Services\FirebaseTokenVerifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        FirebaseTokenVerifier::resetFake();
        parent::tearDown();
    }

    public function test_firebase_session_rejects_missing_id_token(): void
    {
        $response = $this->postJson('/auth/firebase-session', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['id_token']);
        $this->assertFalse(Auth::check());
    }

    public function test_firebase_session_rejects_unverified_or_forged_id_token(): void
    {
        FirebaseTokenVerifier::fake(null); // Return null simulating failed verification

        $response = $this->postJson('/auth/firebase-session', [
            'id_token' => 'forged.or.invalid.token',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'success' => false,
            'message' => 'Invalid, expired, or unverified Firebase ID token.',
        ]);
        $this->assertFalse(Auth::check());
    }

    public function test_stripe_success_refuses_activation_for_nonexistent_transaction(): void
    {
        $user = User::create([
            'name' => 'Stripe User',
            'email' => 'stripe_user@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'invoice_credits' => 5,
        ]);

        $response = $this->actingAs($user)->get('/stripe/success?session_id=fake_nonexistent_session');

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error', 'Invalid checkout session.');
        $user->refresh();
        $this->assertEquals(5, $user->invoice_credits);
        $this->assertNull($user->package_id);
    }

    public function test_firebase_session_authenticates_with_verified_token_claims(): void
    {
        FirebaseTokenVerifier::fake([
            'uid' => 'firebase_verified_sub_999',
            'email' => 'legit_user@example.com',
            'name' => 'Legit User',
            'avatar_url' => 'https://example.com/avatar.jpg',
        ]);

        $response = $this->postJson('/auth/firebase-session', [
            'id_token' => 'header.payload.signature',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'user' => [
                'email' => 'legit_user@example.com',
                'name' => 'Legit User',
            ],
        ]);

        $this->assertTrue(Auth::check());
        $this->assertEquals('legit_user@example.com', Auth::user()->email);
        $this->assertEquals('firebase_verified_sub_999', Auth::user()->firebase_uid);
    }

    public function test_client_portal_access_rejects_expired_token(): void
    {
        $user = User::create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'invoice_credits' => 10,
        ]);

        $client = Client::create([
            'user_id' => $user->id,
            'name' => 'Expired Link Client',
            'email' => 'expired@client.com',
            'portal_access_token' => 'expired_token_123',
            'portal_token_expires_at' => now()->subHours(2),
        ]);

        $response = $this->get("/portal/access/{$client->portal_access_token}");

        $response->assertRedirect(route('portal.login'));
        $response->assertSessionHas('error');
        $this->assertNull(session('portal_client_id'));
    }

    public function test_client_portal_access_succeeds_with_valid_token(): void
    {
        $user = User::create([
            'name' => 'Owner',
            'email' => 'owner2@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'invoice_credits' => 10,
        ]);

        $client = Client::create([
            'user_id' => $user->id,
            'name' => 'Valid Link Client',
            'email' => 'valid@client.com',
            'portal_access_token' => 'active_token_456',
            'portal_token_expires_at' => now()->addHours(24),
        ]);

        $response = $this->get("/portal/access/{$client->portal_access_token}");

        $response->assertRedirect(route('portal.dashboard'));
        $this->assertEquals($client->id, session('portal_client_id'));
    }

    public function test_client_portal_send_magic_link_regenerates_token_with_future_expiry(): void
    {
        $user = User::create([
            'name' => 'Owner',
            'email' => 'owner3@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'invoice_credits' => 10,
        ]);

        $client = Client::create([
            'user_id' => $user->id,
            'name' => 'Magic Link Client',
            'email' => 'magic@client.com',
            'portal_access_token' => 'old_token_789',
            'portal_token_expires_at' => now()->subDay(),
        ]);

        $oldToken = $client->portal_access_token;

        $response = $this->post('/portal/request-link', [
            'email' => 'magic@client.com',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $client->refresh();
        $this->assertNotEquals($oldToken, $client->portal_access_token);
        $this->assertTrue($client->portal_token_expires_at->isFuture());
    }

    public function test_demo_login_allowed_in_testing_environment(): void
    {
        $response = $this->get('/auth/demo-login/user');

        $response->assertRedirect(route('dashboard'));
        $this->assertTrue(Auth::check());
    }

    public function test_demo_login_forbidden_in_production(): void
    {
        // Bind application environment to return production
        app()->detectEnvironment(fn () => 'production');

        $response = $this->get('/auth/demo-login/admin');

        $response->assertStatus(403);
    }
}
