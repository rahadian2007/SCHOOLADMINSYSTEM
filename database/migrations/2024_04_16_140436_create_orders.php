<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->integer('total_order_value');
            $table->integer('total_paid');
            $table->string('status');
            $table->unsignedBigInteger('payment_method_id');
            $table->foreign('payment_method_id')
                ->references('id')
                ->on('payment_methods');
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('order_id');
            $table->foreign('order_id')
                ->references('id')
                ->on('orders');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->foreign('product_id')
                ->references('id')
                ->on('products');
            $table->string('alt_name')->nullable();
            $table->string('alt_unit_price')->nullable();
            $table->integer('qty');
            $table->integer('subtotal');
            $table->integer('discount_percent')->nullable();
            $table->integer('discount_nominal')->nullable();
            $table->integer('commission_percent')->nullable();
            $table->integer('commission_nominal')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('payment_methods');
    }
}
