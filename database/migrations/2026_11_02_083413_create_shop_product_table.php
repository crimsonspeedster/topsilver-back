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
        Schema::create('shop_product', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('shop_id');

            $table->foreign('shop_id')->references('id')->on('shops')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

            $table->unique(['product_id', 'shop_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_product');
    }
};
