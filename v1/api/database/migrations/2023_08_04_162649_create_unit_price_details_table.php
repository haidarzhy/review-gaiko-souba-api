<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUnitPriceDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('unit_price_details', function (Blueprint $table) {
            $table->id();
            $table->string('large_classification')->nullable();
            $table->string('minor_classification')->nullable();
            $table->string('content')->nullable();
            $table->string('specification')->nullable();
            $table->unsignedBigInteger('area_id')->nullable();
            $table->string('amount')->nullable();
            $table->unsignedBigInteger('unit_price_id')->nullable();
            $table->boolean('status');
            $table->integer('order');
            $table->timestamps();

            $table->foreign('area_id')->references('id')->on('areas')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('unit_price_id')->references('id')->on('unit_prices')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('unit_price_details');
    }
}
