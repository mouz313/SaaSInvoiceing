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
        // 1. Add FBR POS fields to users table
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('fbr_enabled')->default(false)->after('locale');
            $table->string('fbr_environment', 20)->default('sandbox')->after('fbr_enabled');
            $table->string('fbr_pos_id', 50)->nullable()->after('fbr_environment');
            $table->string('fbr_pos_usin', 50)->nullable()->after('fbr_pos_id');
            $table->text('fbr_bearer_token')->nullable()->after('fbr_pos_usin');
        });

        // 2. Add user_id and client_id to coupons table for merchant-scoped coupons
        Schema::table('coupons', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->after('user_id')->constrained('clients')->nullOnDelete();
        });

        // 3. Add FBR and Coupon tracking fields to invoices table
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('coupon_id')->nullable()->after('discount_amount')->constrained('coupons')->nullOnDelete();
            $table->string('coupon_code', 50)->nullable()->after('coupon_id');

            // FBR Compliance fields
            $table->string('fbr_invoice_number', 60)->nullable()->index()->after('payment_instructions');
            $table->string('fbr_status', 30)->default('not_applicable')->after('fbr_invoice_number');
            $table->timestamp('fbr_synced_at')->nullable()->after('fbr_status');
            $table->text('fbr_qr_code_data')->nullable()->after('fbr_synced_at');
            $table->text('fbr_error_message')->nullable()->after('fbr_qr_code_data');
            $table->string('pct_code', 30)->default('9801.0000')->after('fbr_error_message');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['coupon_id']);
            $table->dropColumn([
                'coupon_id',
                'coupon_code',
                'fbr_invoice_number',
                'fbr_status',
                'fbr_synced_at',
                'fbr_qr_code_data',
                'fbr_error_message',
                'pct_code',
            ]);
        });

        Schema::table('coupons', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['client_id']);
            $table->dropColumn(['user_id', 'client_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'fbr_enabled',
                'fbr_environment',
                'fbr_pos_id',
                'fbr_pos_usin',
                'fbr_bearer_token',
            ]);
        });
    }
};
