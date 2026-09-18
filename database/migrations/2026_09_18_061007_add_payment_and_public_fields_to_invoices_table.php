<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('public_token', 64)->nullable()->unique()->after('invoice_number');
            $table->timestamp('viewed_at')->nullable()->after('status');
            $table->timestamp('paid_at')->nullable()->after('viewed_at');
            $table->string('stripe_payment_intent_id', 100)->nullable()->after('paid_at');
        });

        // Backfill existing invoices with tokens
        $invoices = DB::table('invoices')->whereNull('public_token')->get();
        foreach ($invoices as $invoice) {
            DB::table('invoices')->where('id', $invoice->id)->update([
                'public_token' => Str::random(40),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'public_token',
                'viewed_at',
                'paid_at',
                'stripe_payment_intent_id',
            ]);
        });
    }
};
