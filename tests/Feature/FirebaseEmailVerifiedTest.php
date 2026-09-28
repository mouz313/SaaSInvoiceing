<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\FirebaseTokenVerifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FirebaseEmailVerifiedTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        FirebaseTokenVerifier::resetFake();
        parent::tearDown();
    }

    public function test_unverified_firebase_email_is_rejected_with_403(): void
    {
        $existingUser = User::factory()->create([
            'email' => 'victim@example.com',
            'password' => bcrypt('password123'),
        ]);

        FirebaseTokenVerifier::fake([
            'uid' => 'attacker_uid_123',
            'email' => 'victim@example.com',
            'email_verified' => false,
            'name' => 'Attacker Fake',
            'avatar_url' => null,
        ]);

        $response = $this->postJson('/auth/firebase-session', [
            'id_token' => 'fake_unverified_token',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Please verify your email address before signing in.',
            ]);

        $this->assertGuest();
        $this->assertDatabaseMissing('users', [
            'email' => 'victim@example.com',
            'firebase_uid' => 'attacker_uid_123',
        ]);
    }

    public function test_verified_firebase_email_can_authenticate_and_link(): void
    {
        $user = User::factory()->create([
            'email' => 'legit@example.com',
            'firebase_uid' => null,
        ]);

        FirebaseTokenVerifier::fake([
            'uid' => 'google_uid_999',
            'email' => 'legit@example.com',
            'email_verified' => true,
            'name' => 'Legit User',
            'avatar_url' => null,
        ]);

        $response = $this->postJson('/auth/firebase-session', [
            'id_token' => 'fake_verified_token',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertAuthenticatedAs($user);
        $this->assertEquals('google_uid_999', $user->fresh()->firebase_uid);
    }
}
