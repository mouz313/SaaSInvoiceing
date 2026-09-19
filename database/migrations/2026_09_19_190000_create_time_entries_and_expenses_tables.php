<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->string('project_name')->nullable();
            $table->text('task_description');
            $table->decimal('hours', 8, 2)->default(1.00);
            $table->decimal('hourly_rate', 10, 2)->default(0.00);
            $table->decimal('total_amount', 10, 2)->default(0.00);
            $table->date('date');
            $table->boolean('is_billed')->default(false);
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->string('category')->default('General');
            $table->string('description');
            $table->decimal('amount', 10, 2)->default(0.00);
            $table->string('currency', 10)->default('USD');
            $table->date('expense_date');
            $table->string('receipt_url')->nullable();
            $table->boolean('is_billable')->default(true);
            $table->boolean('is_billed')->default(false);
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('time_entries');
    }
};
