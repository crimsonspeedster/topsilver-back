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
        Schema::create('filter_page_filters', function (Blueprint $table) {
            $table->unsignedBigInteger('filter_page_id')->index();
            $table->unsignedBigInteger('attribute_id')->index();
            $table->unsignedBigInteger('attribute_term_id')->index();

            $table->foreign('filter_page_id')->references('id')->on('filter_pages')->onDelete('cascade');
            $table->foreign('attribute_id')->references('id')->on('attributes')->onDelete('cascade');
            $table->foreign('attribute_term_id')->references('id')->on('attribute_terms')->onDelete('cascade');

            $table->primary(['filter_page_id', 'attribute_id', 'attribute_term_id']);
            $table->index(['filter_page_id', 'attribute_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('filter_page_filters');
    }
};
