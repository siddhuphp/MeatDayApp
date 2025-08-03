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
        Schema::create('reward_points', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id'); // Changed from customer_id to user_id
            $table->integer('points');
            $table->enum('point_type', ['regular', 'pre_order'])->default('regular');
            $table->uuid('transaction_id')->nullable(); // Reference to transaction that earned points
            $table->boolean('redeemed')->default(false);
            $table->timestamp('redeem_date')->nullable();
            $table->timestamps();
        
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('cascade');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reward_points');
    }
};
