<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ShipCabin extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ShipCabin',function(Blueprint $table){
            $table->bigIncrements('id')->unsigned();
            $table->bigInteger('shipId')->unsigned();
            $table->bigInteger('categoryId')->unsigned()->nullable();
            $table->bigInteger('deckId')->unsigned()->nullable();
            $table->tinyInteger('number');
            $table->string('type',255)->nullable();
            $table->text('description')->nullable();

            $table->unique(['shipId','number']);
            $table->foreign('shipId')->references('id')->on('Ship')->onDelete('CASCADE')->onUpdate('CASCADE');
        });

        Schema::table('CruiseCabin',function(Blueprint $table){
            $table->foreign('cabinId')->references('id')->on('ShipCabin')->onDelete('CASCADE')->onUpdate('CASCADE');
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
