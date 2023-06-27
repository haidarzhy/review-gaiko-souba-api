<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInquiryQaAnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inquiry_qa_ans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inquiry_id')->nullable();
            $table->string('q_index')->nullable();
            $table->unsignedBigInteger('qq_id')->nullable();
            $table->unsignedBigInteger('qa_id')->nullable();
            $table->string('qa_value')->nullable();
            $table->timestamps();

            $table->foreign('inquiry_id')->references('id')->on('inquiries')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('qq_id')->references('id')->on('qqs')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('qa_id')->references('id')->on('qas')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inquiry_qa_ans');
    }
}
