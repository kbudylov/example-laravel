<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CruiseCabinCategoryCount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('CruiseCabinCategory',function (Blueprint $table){
            $table->bigIncrements('id')->unsigned();
            $table->bigInteger('cruiseId')->unsigned();
            $table->bigInteger('categoryId')->unsigned();
            $table->integer('countAvailable')->defaultValue(0);

            $table->foreign('cruiseId')->references('id')->on('Cruise')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('categoryId')->references('id')->on('ShipCabinCategory')->onUpdate('CASCADE')->onDelete('CASCADE');
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
