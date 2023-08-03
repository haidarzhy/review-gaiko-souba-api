<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuotationConditionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quotation_conditions', function (Blueprint $table) {
            $table->id();
            $table->string('condition_id')->nullable();
            $table->unsignedBigInteger('qq_id')->nullable();
            $table->unsignedBigInteger('math_symbol_id')->nullable();
            $table->unsignedBigInteger('qa_id')->nullable();
            $table->boolean('qa_any')->default(0);
            $table->unsignedBigInteger('qa_value')->nullable();
            $table->unsignedBigInteger('quotation_id')->nullable();
            $table->timestamps();

            $table->foreign('qq_id')->references('id')->on('qqs')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('math_symbol_id')->references('id')->on('math_symbols')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('qa_id')->references('id')->on('qas')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('quotation_id')->references('id')->on('quotations')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quotation_conditions');
    }
}
