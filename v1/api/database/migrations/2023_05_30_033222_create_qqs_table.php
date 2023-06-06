<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQqsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('qqs', function (Blueprint $table) {
            $table->id();
            $table->text('q')->nullable();
            $table->text('prefix')->nullable();
            $table->unsignedBigInteger('q_ans_input_type_id')->nullable();
            $table->boolean('status');
            $table->integer('order');
            $table->timestamps();

            $table->foreign('q_ans_input_type_id')->references('id')->on('q_ans_input_types')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('qqs');
    }
}
