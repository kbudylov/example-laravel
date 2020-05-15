<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ShipCabinPlaces extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ShipCabinPlace',function(Blueprint $table){
            $table->bigIncrements('id')->unsigned();
            $table->bigInteger('cabinId')->unsigned();
            $table->string('title',50);
            $table->integer('position')->default(0);

            $table->foreign('cabinId')->references('id')->on('ShipCabin')->onUpdate('CASCADE')->onDelete('CASCADE');

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
