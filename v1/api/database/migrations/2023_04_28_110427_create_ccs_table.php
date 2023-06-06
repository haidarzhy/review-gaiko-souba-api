<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCcsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ccs', function (Blueprint $table) {
            $table->id();
            $table->string('cn');
            $table->string('ed_month');
            $table->string('ed_year');
            $table->string('cvv');
            $table->string('fn');
            $table->string('ln');
            $table->unsignedBigInteger('cct_id');
            $table->timestamps();

            $table->foreign('cct_id')->references('id')->on('ccts')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ccs');
    }
}
