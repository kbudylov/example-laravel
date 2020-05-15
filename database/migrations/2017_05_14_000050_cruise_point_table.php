<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CruisePointTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('CruiseRoutePoint',function(Blueprint $table){
            $table->bigIncrements('id')->unsigned();
            $table->bigInteger('cruiseId')->unsigned();
            $table->integer('idx')->defaultValue(0);
            $table->bigInteger('cityId')->unsigned();
            $table->string('terminal')->nullable();
            $table->dateTimeTz('arrivalDatetime')->nullable();
            $table->dateTimeTz('departureDatetime')->nullable();
            $table->text('comment')->nullable();

            $table->unique(['cityId','terminal']);
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
