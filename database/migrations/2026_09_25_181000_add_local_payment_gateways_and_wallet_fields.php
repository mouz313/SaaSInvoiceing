<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add local Pakistani bank and mobile wallet details to users table
        Schema::table('users', function (Blueprint $table) {
            // Bank Transfer / IBFT
            $table->string('bank_name', 100)->nullable()->after('cnic');
            $table->string('bank_account_title', 150)->nullable()->after('bank_name');
            $table->string('bank_account_number', 50)->nullable()->after('bank_account_title');
            $table->string('bank_iban', 50)->nullable()->after('bank_account_number');
            $table->string('raast_id', 50)->nullable()->after('bank_iban');

            // Mobile Wallets (Manual instructions)
            $table->string('jazzcash_number', 30)->nullable()->after('raast_id');
            $table->string('jazzcash_title', 100)->nullable()->after('jazzcash_number');
            $table->string('easypaisa_number', 30)->nullable()->after('jazzcash_title');
            $table->string('easypaisa_title', 100)->nullable()->after('easypaisa_number');

            // Automated Payment Gateway Credentials
            $table->string('jazzcash_merchant_id', 100)->nullable()->after('easypaisa_title');
            $table->string('jazzcash_password', 100)->nullable()->after('jazzcash_merchant_id');
            $table->string('jazzcash_hash_key', 255)->nullable()->after('jazzcash_password');
            $table->string('easypaisa_store_id', 100)->nullable()->after('jazzcash_hash_key');
            $table->string('easypaisa_hash_key', 255)->nullable()->after('easypaisa_store_id');

            // Preferred Locale
            $table->string('locale', 10)->default('en')->after('easypaisa_hash_key');
        });

        // 2. Add verification status and receipt proof file to invoice_payments
        Schema::table('invoice_payments', function (Blueprint $table) {
            $table->string('status', 30)->default('completed')->after('payment_method'); // completed, pending_verification, rejected
            $table->string('proof_file', 255)->nullable()->after('reference_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_payments', function (Blueprint $table) {
            $table->dropColumn(['status', 'proof_file']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'bank_name',
                'bank_account_title',
                'bank_account_number',
                'bank_iban',
                'raast_id',
                'jazzcash_number',
                'jazzcash_title',
                'easypaisa_number',
                'easypaisa_title',
                'jazzcash_merchant_id',
                'jazzcash_password',
                'jazzcash_hash_key',
                'easypaisa_store_id',
                'easypaisa_hash_key',
                'locale',
            ]);
        });
    }
};
