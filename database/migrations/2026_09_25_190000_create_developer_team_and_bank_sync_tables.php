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
        // 1. API Keys Table
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('key_hash', 64)->unique();
            $table->string('key_prefix', 12);
            $table->json('abilities')->nullable(); // e.g. ["invoices:read", "invoices:write"]
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        // 2. Webhooks Table
        Schema::create('webhooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('url');
            $table->string('secret', 64);
            $table->json('events'); // ["invoice.created", "invoice.paid", "invoice.payment_received"]
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_triggered_at')->nullable();
            $table->timestamps();
        });

        // 3. Webhook Delivery Logs Table
        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('webhook_id')->constrained()->cascadeOnDelete();
            $table->string('event');
            $table->json('payload');
            $table->integer('response_status')->nullable();
            $table->text('response_body')->nullable();
            $table->boolean('successful')->default(false);
            $table->timestamps();
        });

        // 4. Team Members Table (Multi-User Roles)
        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role')->default('accountant'); // admin, accountant, viewer
            $table->string('status')->default('active'); // active, pending, suspended
            $table->timestamps();

            $table->unique(['owner_id', 'user_id']);
        });

        // 5. Bank Statements / Transactions Table
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('bank_name')->default('generic'); // meezan, hbl, alfalah, generic
            $table->date('transaction_date');
            $table->string('reference_number')->nullable()->index();
            $table->string('description');
            $table->decimal('amount', 15, 2); // positive for incoming credit
            $table->enum('type', ['credit', 'debit'])->default('credit');
            $table->foreignId('matched_invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->enum('status', ['unreconciled', 'matched', 'ignored'])->default('unreconciled');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_transactions');
        Schema::dropIfExists('team_members');
        Schema::dropIfExists('webhook_logs');
        Schema::dropIfExists('webhooks');
        Schema::dropIfExists('api_keys');
    }
};
