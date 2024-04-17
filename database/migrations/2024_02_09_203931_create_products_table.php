<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('img_url')->nullable();
            $table->timestamps();
        });

        Schema::create('product_vendors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('img_url')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });

        Schema::create('product_vendor_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('base_price')->nullable();
            $table->integer('selling_price');
            $table->integer('discount_percent')->nullable();
            $table->integer('discount_nominal')->nullable();
            $table->integer('commission_percent')->nullable();
            $table->integer('commission_nominal')->nullable();
            $table->string('feat_product_img_url')->nullable();
            $table->unsignedBigInteger('product_category_id');
            $table->foreign('product_category_id')
                ->references('id')
                ->on('product_categories')
                ->nullable();
            $table->unsignedBigInteger('product_vendor_id');
            $table->foreign('product_vendor_id')
                ->references('id')
                ->on('product_vendors')
                ->nullable();
            $table->integer('stock')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
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
        Schema::dropIfExists('products');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('product_categories');
        Schema::dropIfExists('product_vendors');
    }
}
