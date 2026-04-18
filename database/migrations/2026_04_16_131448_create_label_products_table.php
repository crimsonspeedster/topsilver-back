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
        Schema::create('label_products', function (Blueprint $table) {
            $table->unsignedBigInteger('label_id');
            $table->unsignedBigInteger('product_id');

            $table->foreign('label_id')->references('id')->on('labels')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

            $table->primary(['product_id', 'label_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('label_products');
    }
};
