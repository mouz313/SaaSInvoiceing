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
        Schema::create('recurring_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('logo_id')->nullable()->constrained('user_logos')->nullOnDelete();
            $table->string('title');
            $table->string('frequency', 30)->default('monthly'); // weekly, biweekly, monthly, quarterly, yearly
            $table->date('start_date');
            $table->date('next_issue_date');
            $table->date('end_date')->nullable();
            $table->integer('due_days')->default(14);
            $table->string('currency', 10)->default('USD');
            $table->string('style', 30)->default('minimalist');
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('discount_rate', 5, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->json('additional_charges')->nullable();
            $table->decimal('additional_charges_total', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->text('payment_instructions')->nullable();
            $table->boolean('auto_send_email')->default(true);
            $table->string('status', 20)->default('active'); // active, paused, completed
            $table->dateTime('last_generated_at')->nullable();
            $table->unsignedInteger('invoices_generated_count')->default(0);
            $table->timestamps();
        });

        Schema::create('recurring_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recurring_invoice_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('amount', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('recurring_invoice_id')->nullable()->after('client_id')->constrained('recurring_invoices')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['recurring_invoice_id']);
            $table->dropColumn('recurring_invoice_id');
        });

        Schema::dropIfExists('recurring_invoice_items');
        Schema::dropIfExists('recurring_invoices');
    }
};
