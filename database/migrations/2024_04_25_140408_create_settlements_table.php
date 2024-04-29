<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettlementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settlements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_vendor_id');
            $table->foreign('product_vendor_id')
                ->references('id')
                ->on('product_vendors');
            $table->string('status')
                ->nullable();
            $table->integer('settlement_revenue');
            $table->integer('settlement_commission');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('notes')
                ->nullable();
            $table->timestamps();
        });

        Schema::create('settlement_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('settlement_id');
            $table->foreign('settlement_id')
                ->references('id')
                ->on('settlements');
            $table->string('order_item_id');
            $table->foreign('order_item_id')
                ->references('id')
                ->on('order_items');
            $table->string('info')
                ->nullable();
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
        Schema::dropIfExists('settlement_order_items');
        Schema::dropIfExists('settlements');
    }
}
