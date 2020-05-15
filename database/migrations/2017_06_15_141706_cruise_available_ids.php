<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CruiseAvailableIds extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('CruiseAvailableIds',function (Blueprint $table){
            $table->bigIncrements('id')->unsigned();
            $table->bigInteger('cruiseId')->unsigned();

            $table->unique('cruiseId');
            //$table->foreign('cruiseId')->references('Cruise')->on('id')->onUpdate('CASCADE')->onDelete('CASCADE');
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
