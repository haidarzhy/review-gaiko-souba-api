<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInquiriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->nullable();
            $table->string('name')->nullable();
            $table->string('kata_name')->nullable();
            $table->text('address01')->nullable();
            $table->text('address02')->nullable();
            $table->string('company_name')->nullable();
            $table->string('email')->nullable();
            $table->string('tel')->nullable();
            $table->string('construction_schedule')->nullable();
            $table->string('total')->nullable();
            $table->boolean('confirm')->default(0);
            $table->boolean('status');
            $table->integer('order');
            $table->unsignedBigInteger('user_id')->nullable();
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
        Schema::dropIfExists('inquiries');
    }
}
