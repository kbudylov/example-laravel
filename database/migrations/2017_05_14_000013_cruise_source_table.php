<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CruiseSourceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('CruiseSource',function(Blueprint $table){
            $table->bigIncrements('id')->unsigned();
            $table->string('title',50);
            $table->string('prefix',50);
            $table->text('config')->nullable();

            $table->unique('title');
            $table->unique('prefix');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
