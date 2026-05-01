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
        Schema::create('seo_blocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('entity_id');
            $table->string('entity_type');
            $table->string('title');
            $table->text('excerpt')->nullable();
            $table->text('content');
            $table->timestamps();

            $table->unique(['entity_id', 'entity_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seo_blocks');
    }
};
