<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateColumnsNullableInCcsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ccs', function (Blueprint $table) {
            $table->string('cn')->nullable()->change();
            $table->string('ed_month')->nullable()->change();
            $table->string('ed_year')->nullable()->change();
            $table->string('cvv')->nullable()->change();
            $table->string('fn')->nullable()->change();
            $table->string('ln')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ccs', function (Blueprint $table) {
            $table->string('cn')->change();
            $table->string('ed_month')->change();
            $table->string('ed_year')->change();
            $table->string('cvv')->change();
            $table->string('fn')->change();
            $table->string('ln')->change();
        });
    }
}
