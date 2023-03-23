<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('partnerServiceId', 8);
            $table->string('customerNo', 20);
            $table->string('virtualAccountNumber', 28);
            $table->string('virtualAccountName', 255);
            $table->string('virtualAccountEmail', 255)->nullable();
            $table->string('virtualAccountPhone', 30)->nullable();
            $table->string('trxId', 64);
            $table->string('paymentRequestId', 128);
            $table->integer('channelCode');
            $table->string('hashedSourceAccountNo', 32)->nullable();
            $table->string('sourceBankCode', 3)->nullable();
            $table->json('paidAmount');
            $table->json('cumulativePaymentAmount')->nullable();
            $table->json('totalAmount')->nullable();
            $table->string('paidBills', 6)->nullable();
            $table->date('trxDateTime')->nullable();
            $table->string('referenceNo', 64)->nullable();
            $table->string('journalNum', 6)->nullable();
            $table->string('paymentTypee', 1)->nullable();
            $table->string('flagAdvise', 1)->nullable();
            $table->string('subCompany', 5)->nullable();
            $table->json('billDetails')->nullable();
            $table->json('freeTexts')->nullable();
            $table->json('additionalInfo')->nullable();
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
        Schema::dropIfExists('virtual_accounts');
    }
}
