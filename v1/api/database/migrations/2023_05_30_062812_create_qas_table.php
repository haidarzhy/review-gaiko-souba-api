<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('qas', function (Blueprint $table) {
            $table->id();
            $table->string('suffix')->nullable();
            $table->text('label')->nullable();
            $table->text('image')->nullable();
            $table->string('quantity')->nullable();
            $table->string('unit_price')->nullable();
            $table->unsignedBigInteger('qq_id')->nullable();
            $table->boolean('control')->default(0);
            $table->unsignedBigInteger('controlled_id')->nullable();
            $table->boolean('status');
            $table->integer('order');
            $table->timestamps();

            $table->foreign('qq_id')->references('id')->on('qqs')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('qas');
    }
}
