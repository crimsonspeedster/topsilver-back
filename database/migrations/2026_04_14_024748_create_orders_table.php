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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();

            $table->string('status');

            $table->decimal('subtotal', 10, 2);
            $table->decimal('total', 10, 2);
            $table->timestamp('paid_at')->nullable();

            $table->string('coupon_code')->nullable();
            $table->string('coupon_type')->nullable();
            $table->decimal('coupon_value', 10, 2)->nullable();
            $table->decimal('discount_amount', 10, 2)->default(0);

            $table->text('notes')->nullable();

            $table->string('first_name');
            $table->string('last_name');
            $table->string('middle_name')->nullable();
            $table->string('phone');
            $table->string('email')->nullable();

            $table->string('payment_type');
            $table->json('payment_data')->nullable();

            $table->string('shipping_type');
            $table->json('shipping_data')->nullable();

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

            $table->index('status');
            $table->index('paid_at');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
