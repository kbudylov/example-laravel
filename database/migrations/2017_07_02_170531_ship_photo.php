<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ShipPhoto extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ShipPhoto',function(Blueprint $table){
            $table->bigIncrements('id')->unsigned();
            $table->bigInteger('shipId')->unsigned();
            $table->string('imageUrl',255);
            $table->string('thumbUrl',255)->nullable();

            $table->foreign('shipId')->references('id')->on('Ship')->onDelete('CASCADE')->onUpdate('CASCADE');
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
