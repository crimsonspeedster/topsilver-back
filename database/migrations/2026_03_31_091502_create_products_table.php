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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('group_key')->nullable();
            $table->string('sku')->nullable()->unique();
            $table->string('status')->default('draft');
            $table->decimal('rating_avg', 3, 2)->default(0);
            $table->unsignedInteger('rating_count')->default(0);
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();
            $table->decimal('price', 8, 2)->nullable();
            $table->decimal('price_on_sale', 8, 2)->nullable();
            $table->boolean('manage_stock')->default(false);
            $table->unsignedInteger('stock')->nullable();
            $table->string('stock_status')->default('in_stock');
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('selling_count')->default(0);
            $table->timestamps();

            $table->index(['group_key']);
            $table->index(['status', 'published_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
