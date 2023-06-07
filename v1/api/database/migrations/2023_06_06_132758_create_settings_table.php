<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('site_name')->nullable();
            $table->text('description')->nullable();
            $table->text('keywords')->nullable();
            $table->text('site_logo')->nullable();
            $table->text('icon')->nullable();
            $table->string('email')->nullable();
            $table->text('footer_text')->nullable();
            $table->string('site_size')->nullable();
            $table->string('cache_size')->nullable();
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
        Schema::dropIfExists('settings');
    }
}
