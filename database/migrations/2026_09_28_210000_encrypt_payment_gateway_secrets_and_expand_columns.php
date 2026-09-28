<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Expand columns to TEXT to safely store base64 encrypted payloads
        Schema::table('users', function (Blueprint $table) {
            $table->text('jazzcash_merchant_id')->nullable()->change();
            $table->text('jazzcash_password')->nullable()->change();
            $table->text('jazzcash_hash_key')->nullable()->change();
            $table->text('easypaisa_store_id')->nullable()->change();
            $table->text('easypaisa_hash_key')->nullable()->change();
        });

        // 2. Re-encrypt any existing plaintext values
        $users = DB::table('users')->select([
            'id',
            'jazzcash_merchant_id',
            'jazzcash_password',
            'jazzcash_hash_key',
            'easypaisa_store_id',
            'easypaisa_hash_key',
        ])->get();

        foreach ($users as $user) {
            $updates = [];

            foreach ([
                'jazzcash_merchant_id',
                'jazzcash_password',
                'jazzcash_hash_key',
                'easypaisa_store_id',
                'easypaisa_hash_key',
            ] as $field) {
                $val = $user->{$field};
                if (! empty($val)) {
                    // Check if already encrypted
                    try {
                        Crypt::decryptString($val);
                    } catch (Throwable) {
                        // Value was in plaintext, encrypt it now
                        $updates[$field] = Crypt::encryptString($val);
                    }
                }
            }

            if (! empty($updates)) {
                DB::table('users')->where('id', $user->id)->update($updates);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('jazzcash_merchant_id', 100)->nullable()->change();
            $table->string('jazzcash_password', 100)->nullable()->change();
            $table->string('jazzcash_hash_key', 255)->nullable()->change();
            $table->string('easypaisa_store_id', 100)->nullable()->change();
            $table->string('easypaisa_hash_key', 255)->nullable()->change();
        });
    }
};
