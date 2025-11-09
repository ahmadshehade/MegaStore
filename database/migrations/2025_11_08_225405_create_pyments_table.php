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
        Schema::create('pyments', function (Blueprint $table) {
         $table->id();

            // FK to orders
            $table->foreignId('order_id')
                  ->constrained('orders')
                  ->onDelete('cascade');

            // FK to payment_methods table
            $table->foreignId('payment_method_id')
                  ->constrained('payment_methods')
                  ->onDelete('restrict');

            // Transaction ID returned by external payment provider (e.g., Stripe / PayPal)
            $table->string('provider_transaction_id')->nullable()->index();

            // Payment details
            $table->decimal('amount', 12, 2); // store money accurately
            $table->string('currency', 10)->default('USD');

            // Payment status
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded'])
                  ->default('pending')
                  ->index();

            // Generic field to store provider payload / logs
            $table->json('meta')->nullable();

            // When the payment was successfully completed
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('order_id');
            $table->index('payment_method_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pyments');
    }
};
