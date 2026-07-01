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
        Schema::create('integration_batch_errors', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('integration_batch_id');

            $table->string('external_id')->nullable();
            $table->unsignedInteger('item_index')->nullable();

            $table->string('field')->nullable();
            $table->string('code');
            $table->text('message');

            $table->timestamps();

            $table->foreign('integration_batch_id')->references('id')->on('integration_batches')->onDelete('cascade');

            $table->index('integration_batch_id');
            $table->index('external_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('integration_batch_errors');
    }
};
