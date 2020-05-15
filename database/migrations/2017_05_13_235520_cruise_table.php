<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CruiseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Cruise',function(Blueprint $table){
            $table->bigIncrements('id')->unsigned();
            $table->bigInteger('sourceId')->unsigned();
            $table->string('title',50);
            $table->bigInteger('departurePointId')->unsigned();
            $table->bigInteger('returnPointId')->unsigned();
            $table->dateTimeTz('departureDateTime')->nullable();
            $table->dateTimeTz('returnTime')->nullable();
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
