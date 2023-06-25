<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_infos', function (Blueprint $table) {
            $table->id();
            $table->string('plan')->nullable();
            $table->string('price')->nullable();
            $table->string('gid')->nullable();
            $table->string('rst')->nullable();
            $table->string('ap')->nullable();
            $table->string('ec')->nullable();
            $table->string('god')->nullable();
            $table->string('cod')->nullable();
            $table->string('am')->nullable();
            $table->string('tx')->nullable();
            $table->string('sf')->nullable();
            $table->string('ta')->nullable();
            $table->string('issue_id')->nullable();
            $table->string('ps')->nullable();
            $table->string('others')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_infos');
    }
}
