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
        Schema::create('invoice_templates', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 50)->unique();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->string('category', 50)->default('General');
            $table->decimal('price', 8, 2)->default(0.00);
            $table->string('currency', 10)->default('USD');
            $table->boolean('is_free')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('preview_image')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('user_template_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('template_id')->constrained('invoice_templates')->cascadeOnDelete();
            $table->decimal('price_paid', 8, 2)->default(0.00);
            $table->string('currency', 10)->default('USD');
            $table->string('payment_method', 50)->default('stripe');
            $table->string('transaction_id')->nullable();
            $table->timestamp('purchased_at')->useCurrent();
            $table->timestamps();

            $table->unique(['user_id', 'template_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_template_purchases');
        Schema::dropIfExists('invoice_templates');
    }
};
