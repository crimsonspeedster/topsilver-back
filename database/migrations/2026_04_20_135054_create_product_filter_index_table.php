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
        Schema::create('product_filter_index', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->index();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('collection_id')->nullable();
            $table->unsignedBigInteger('attribute_id')->nullable();
            $table->unsignedBigInteger('attribute_term_id')->nullable();
            $table->boolean('is_variant')->default(false);
            $table->decimal('price', 8, 2)->nullable()->index();
            $table->string('stock_status')->default('in_stock')->index();
            $table->timestamps();

            $table->index('attribute_term_id');

            $table->index([
                'category_id',
                'attribute_term_id',
            ]);

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
            $table->foreign('collection_id')->references('id')->on('collections')->onDelete('set null');
            $table->foreign('attribute_id')->references('id')->on('attributes')->onDelete('set null');
            $table->foreign('attribute_term_id')->references('id')->on('attribute_terms')->onDelete('set null');

            $table->unique([
                'product_id',
                'category_id',
                'collection_id',
                'attribute_term_id',
                'is_variant'
            ],
            'product_filter_index_unique_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_filter_index');
    }
};
