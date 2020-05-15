<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CruiseRouteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('CruiseRoute',function(Blueprint $table){
            $table->bigIncrements('id')->unsigned();
            $table->bigInteger('cruiseId')->unsigned();
            $table->bigInteger('pointId')->unsigned();
            $table->tinyInteger('index')->default(0);
            $table->dateTimeTz('arrivalDateTime')->nullable();
            $table->dateTimeTz('departureDateTime')->nullable();
            $table->text('description')->nullable();
            $table->tinyInteger('isStart')->default(0);
            $table->tinyInteger('isEnd')->default(0);

            $table->foreign('cruiseId')->references('id')->on('Cruise')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->foreign('pointId')->references('id')->on('CruiseRoutePoint')->onDelete('CASCADE')->onUpdate('CASCADE');
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
