<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GatewaySecretsEncryptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_gateway_secrets_are_stored_encrypted_in_database(): void
    {
        $user = User::factory()->create([
            'jazzcash_merchant_id' => 'MC_SECRET_123',
            'jazzcash_password' => 'SuperSecretPass#99',
            'jazzcash_hash_key' => 'IntegritySalt987654321',
            'easypaisa_store_id' => 'EP_STORE_555',
            'easypaisa_hash_key' => 'EasyPaisaSecretHash!@#',
        ]);

        // Raw database inspection
        $raw = DB::table('users')->where('id', $user->id)->first();

        $this->assertNotEquals('SuperSecretPass#99', $raw->jazzcash_password);
        $this->assertNotEquals('IntegritySalt987654321', $raw->jazzcash_hash_key);
        $this->assertNotEquals('EasyPaisaSecretHash!@#', $raw->easypaisa_hash_key);
        $this->assertNotEquals('MC_SECRET_123', $raw->jazzcash_merchant_id);
        $this->assertNotEquals('EP_STORE_555', $raw->easypaisa_store_id);

        // Decrypts transparently when accessed via Eloquent
        $fresh = User::find($user->id);
        $this->assertEquals('SuperSecretPass#99', $fresh->jazzcash_password);
        $this->assertEquals('IntegritySalt987654321', $fresh->jazzcash_hash_key);
        $this->assertEquals('EasyPaisaSecretHash!@#', $fresh->easypaisa_hash_key);
        $this->assertEquals('MC_SECRET_123', $fresh->jazzcash_merchant_id);
        $this->assertEquals('EP_STORE_555', $fresh->easypaisa_store_id);

        // Not present in JSON serialization
        $json = $fresh->toArray();
        $this->assertArrayNotHasKey('jazzcash_password', $json);
        $this->assertArrayNotHasKey('jazzcash_hash_key', $json);
        $this->assertArrayNotHasKey('easypaisa_hash_key', $json);
        $this->assertArrayNotHasKey('jazzcash_merchant_id', $json);
        $this->assertArrayNotHasKey('easypaisa_store_id', $json);
    }
}
