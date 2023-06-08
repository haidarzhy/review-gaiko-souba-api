<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQaudsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('qauds', function (Blueprint $table) {
            $table->id();
            $table->string('quantity')->nullable();
            $table->string('unit_price')->nullable();
            $table->unsignedBigInteger('qa_id')->nullable();
            $table->timestamps();

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
        Schema::dropIfExists('qauds');
    }
}
