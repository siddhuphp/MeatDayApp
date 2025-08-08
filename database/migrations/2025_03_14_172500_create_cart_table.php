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
        Schema::create('cart', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id'); // Using user_id instead of customer_id for consistency
            $table->uuid('product_id');
            $table->decimal('quantity', 8, 3); // Supports fractional kg (e.g., 0.500 for 500g)
            $table->decimal('unit_price', 10, 2); // Price per kg from product
            $table->decimal('discount_percentage', 5, 2)->default(0); // Product discount percentage
            $table->decimal('discount_amount', 10, 2)->default(0); // Calculated discount amount
            $table->decimal('final_price', 10, 2); // Price after discount
            $table->decimal('total_price', 10, 2); // Quantity * final_price
            $table->integer('regular_points')->default(0); // Points for immediate purchase
            $table->integer('pre_order_points')->default(0); // Points for pre-order
            $table->timestamps();

            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            
            // Ensure unique combination of user and product
            $table->unique(['user_id', 'product_id'], 'cart_unique_item');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart');
    }
};
