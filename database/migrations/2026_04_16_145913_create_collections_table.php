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
        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->unique();
            $table->string('parent_external_id')->nullable();
            $table->string('title');
            $table->string('status')->default('draft');
            $table->text('description')->nullable();
            $table->json('content')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('collections')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collections');
    }
};
