<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('kana_name')->nullable();
            $table->string('company_name')->nullable();
            $table->string('tel')->nullable();
            $table->string('email')->nullable();
            $table->text('address01')->nullable();
            $table->text('address02')->nullable();
            $table->text('content')->nullable();
            $table->string('site')->nullable();
            $table->string('ip')->nullable();
            $table->string('lat')->nullable();
            $table->string('lon')->nullable();
            $table->string('continent')->nullable();
            $table->string('country')->nullable();
            $table->string('regionName')->nullable();
            $table->string('city')->nullable();
            $table->boolean('mobile')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->boolean('status');
            $table->integer('order');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contacts');
    }
}
