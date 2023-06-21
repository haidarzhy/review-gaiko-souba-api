<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuotationFormulaConditionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quotation_formula_conditions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('math_symbol_id')->nullable();
            $table->string('situation')->nullable();
            $table->string('result')->nullable();
            $table->unsignedBigInteger('quotation_formula_id')->nullable();
            $table->timestamps();

            $table->foreign('math_symbol_id')->references('id')->on('math_symbols')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('quotation_formula_id')->references('id')->on('quotation_formulas')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quotation_formula_conditions');
    }
}
