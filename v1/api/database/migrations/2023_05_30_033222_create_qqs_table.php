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
            $table->text('suffix')->nullable();
            $table->unsignedBigInteger('q_ans_input_type_id')->nullable();
            $table->text('choice')->nullable();
            $table->boolean('required')->default(0);
            $table->boolean('control')->default(0);
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
