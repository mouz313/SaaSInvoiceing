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
        // 1. Add FBR tax fields (NTN, STRN, CNIC) to users
        Schema::table('users', function (Blueprint $table) {
            $table->string('ntn', 50)->nullable()->after('tax_id');
            $table->string('strn', 50)->nullable()->after('ntn');
            $table->string('cnic', 50)->nullable()->after('strn');
        });

        // 2. Add FBR tax fields to clients
        Schema::table('clients', function (Blueprint $table) {
            $table->string('ntn', 50)->nullable()->after('tax_id');
            $table->string('strn', 50)->nullable()->after('ntn');
            $table->string('cnic', 50)->nullable()->after('strn');
        });

        // 3. Add Withholding Tax (WHT) and Tax Authority to invoices
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('tax_authority', 50)->nullable()->after('tax_amount');
            $table->decimal('wht_rate', 5, 2)->default(0)->after('tax_authority');
            $table->decimal('wht_amount', 12, 2)->default(0)->after('wht_rate');
        });

        // 4. Create coupons table
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->string('discount_type', 20)->default('percentage'); // percentage, fixed
            $table->decimal('discount_value', 10, 2);
            $table->string('applies_to', 30)->default('all'); // all, packages, templates, invoices
            $table->decimal('min_spend', 10, 2)->nullable();
            $table->decimal('max_discount', 10, 2)->nullable();
            $table->unsignedInteger('max_uses')->nullable();
            $table->unsignedInteger('times_used')->default(0);
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 5. Create coupon_usages audit table
        Schema::create('coupon_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained('coupons')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('usable_type')->nullable(); // App\Models\Package, App\Models\InvoiceTemplate, App\Models\Invoice
            $table->unsignedBigInteger('usable_id')->nullable();
            $table->decimal('discount_amount', 10, 2);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['usable_type', 'usable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupon_usages');
        Schema::dropIfExists('coupons');

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['tax_authority', 'wht_rate', 'wht_amount']);
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['ntn', 'strn', 'cnic']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['ntn', 'strn', 'cnic']);
        });
    }
};
