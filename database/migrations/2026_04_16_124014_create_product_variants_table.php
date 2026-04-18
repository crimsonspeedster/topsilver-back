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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('variant_key')->index();
            $table->string('sku')->nullable()->unique();
            $table->decimal('price', 8, 2)->nullable();
            $table->decimal('price_on_sale', 8, 2)->nullable();
            $table->unsignedInteger('stock')->default(0);
            $table->string('stock_status')->default('in_stock');
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->unique(['product_id', 'variant_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
