<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ShipCabinCategory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ShipCabinCategory',function(Blueprint $table){
            $table->bigIncrements('id')->unsigned();
            $table->bigInteger('shipId')->unsigned();
            $table->string('title',255);
            $table->text('description')->nullable();

            $table->foreign('shipId')->references('id')->on('Ship')->onUpdate('CASCADE')->onDelete('CASCADE');
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
