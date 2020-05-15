<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ShipDeck extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ShipDeck',function(Blueprint $table){
            $table->bigIncrements('id')->unsigned();
            $table->bigInteger('shipId')->unsigned();
            $table->string('title',255);
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
