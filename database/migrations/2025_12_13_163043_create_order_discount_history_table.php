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
        Schema::create('order_discount_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('discount_id')->constrained('discounts')->onDelete('cascade');
            $table->decimal('discount_value', 10, 2)->nullable();
            $table->enum('discount_type', ['percentage', 'fixed'])->nullable();
            $table->decimal('total_amount_before_discount', 12, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_discount_history');
    }
};
