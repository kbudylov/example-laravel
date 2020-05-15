<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ShipCabnCategoryPhoto extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ShipCabinCategoryPhoto',function(Blueprint $table){
            $table->bigIncrements('id')->unsigned();
            $table->bigInteger('categoryId')->unsigned();
            $table->string('url',255);
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
