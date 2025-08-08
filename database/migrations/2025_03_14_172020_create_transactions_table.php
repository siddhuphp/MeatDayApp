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
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id'); // Changed from customer_id to user_id
            $table->string('bill_no')->unique();
            $table->decimal('subtotal', 10, 2); // Total before discount
            $table->decimal('total_discount', 10, 2)->default(0); // Total discount applied
            $table->decimal('total_amount', 10, 2); // Final amount after discount
            $table->enum('order_type', ['immediate', 'pre_order'])->default('immediate');
            $table->date('delivery_date')->nullable(); // For pre-orders
            $table->enum('status', ['pending', 'confirmed', 'preparing', 'out_for_delivery', 'delivered', 'cancelled'])->default('pending');
            $table->integer('total_regular_points')->default(0); // Total regular points earned
            $table->integer('total_pre_order_points')->default(0); // Total pre-order points earned
            
            // Payment related fields
            $table->string('payment_method')->default('payu'); // payu, cod, etc.
            $table->enum('payment_status', ['pending', 'success', 'failed', 'cancelled'])->default('pending');
            $table->string('payu_txnid')->nullable(); // PayU transaction ID
            $table->string('payu_hash')->nullable(); // PayU hash for verification
            $table->json('payment_response')->nullable(); // Store PayU response
            $table->timestamp('payment_date')->nullable(); // When payment was completed
            
            $table->timestamps();

            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
