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
        Schema::table('users', function (Blueprint $table) {
            $table->string('company_name')->nullable()->after('name');
            $table->string('phone')->nullable()->after('email');
            $table->text('address')->nullable()->after('avatar_url');
            $table->string('city')->nullable()->after('address');
            $table->string('country')->nullable()->after('city');
            $table->string('tax_id')->nullable()->after('country');
            $table->string('default_currency', 10)->default('USD')->after('tax_id');
            $table->text('default_notes')->nullable()->after('default_currency');
            $table->text('default_payment_instructions')->nullable()->after('default_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'company_name',
                'phone',
                'address',
                'city',
                'country',
                'tax_id',
                'default_currency',
                'default_notes',
                'default_payment_instructions',
            ]);
        });
    }
};
