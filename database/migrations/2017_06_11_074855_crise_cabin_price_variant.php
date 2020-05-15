<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CriseCabinPriceVariant extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('PriceVariant',function(Blueprint $table){
            $table->bigIncrements('id')->unsigned();
            $table->bigInteger('cabinId')->unsigned();
            $table->integer('countPeople')->nullable();
            $table->float('price')->default(0);
            $table->foreign('cabinId')->references('id')->on('CruiseCabin')->onUpdate('CASCADE')->onDelete('CASCADE');
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
