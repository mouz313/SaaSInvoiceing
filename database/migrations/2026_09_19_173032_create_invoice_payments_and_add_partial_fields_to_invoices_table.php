<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('amount_paid', 10, 2)->default(0)->after('total');
            $table->decimal('balance_due', 10, 2)->nullable()->after('amount_paid');
            $table->timestamp('last_reminder_sent_at')->nullable()->after('stripe_payment_intent_id');
            $table->unsignedInteger('reminder_count')->default(0)->after('last_reminder_sent_at');
        });

        // Initialize balance_due and amount_paid for existing invoices
        $invoices = DB::table('invoices')->get();
        foreach ($invoices as $inv) {
            $isPaid = $inv->status === 'paid';
            $paidAmt = $isPaid ? (float) $inv->total : 0.00;
            $balDue = $isPaid ? 0.00 : (float) $inv->total;

            DB::table('invoices')->where('id', $inv->id)->update([
                'amount_paid' => $paidAmt,
                'balance_due' => $balDue,
            ]);
        }

        Schema::create('invoice_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('payment_method', 50)->default('other');
            $table->string('reference_number', 100)->nullable();
            $table->text('notes')->nullable();
            $table->dateTime('paid_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_payments');

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'amount_paid',
                'balance_due',
                'last_reminder_sent_at',
                'reminder_count',
            ]);
        });
    }
};
