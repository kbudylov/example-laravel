<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CruiseRoutePointCitiesIds extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('CruiseRoutePointCitiesIds',function (Blueprint $table){
            $table->bigIncrements('id')->unsigned();
            $table->bigInteger('cruiseId')->unsigned();
            $table->bigInteger('cityId')->unsigned();
            $table->unique(['cruiseId','cityId']);
            //$table->foreign('cruiseId')->references('id')->on('Cruise')->onUpdate('CASCADE')->onDelete('CASCADE');
            //$table->foreign('cityId')->references('id')->on('GEOCity')->onUpdate('CASCADE')->onDelete('CASCADE');
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
