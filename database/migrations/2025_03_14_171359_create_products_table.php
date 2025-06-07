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
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('subcategory_id');
            $table->string('name');
            $table->decimal('price_per_kg', 10, 2);
            $table->decimal('regular_points', 10, 2)->comment('Regular Reward Points')->default(0);
            $table->decimal('pre_order_points', 10, 2)->comment('Pre-Order Reward Points')->default(0);
            $table->decimal('product_discount', 5, 2)->default(0);
            $table->string('product_image')->nullable();
            $table->timestamps();

            $table->foreign('subcategory_id')->references('id')->on('subcategories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
