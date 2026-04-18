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
        Schema::create('attribute_term_variants', function (Blueprint $table) {
            $table->unsignedBigInteger('product_variant_id');
            $table->unsignedBigInteger('attribute_term_id');

            $table->foreign('product_variant_id')->references('id')->on('product_variants')->onDelete('cascade');
            $table->foreign('attribute_term_id')->references('id')->on('attribute_terms')->onDelete('cascade');

            $table->primary(['product_variant_id', 'attribute_term_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attribute_term_variants');
    }
};
