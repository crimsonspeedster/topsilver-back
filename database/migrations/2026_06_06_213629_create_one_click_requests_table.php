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
        Schema::create('one_click_requests', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('product_variant_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();

            $table->string('name');
            $table->string('phone');
            $table->string('email')->nullable();

            $table->text('comment')->nullable();

            $table->string('status');
            $table->string('product_name');
            $table->string('product_image')->nullable();
            $table->json('product_variant')->nullable();
            $table->decimal('total', 10, 2);

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');
            $table->foreign('product_variant_id')->references('id')->on('product_variants')->onDelete('set null');

            $table->timestamps();

            $table->index('status');
            $table->index('phone');
            $table->index('product_id');
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('one_click_requests');
    }
};
