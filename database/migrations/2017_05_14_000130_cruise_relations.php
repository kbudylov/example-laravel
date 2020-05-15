<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CruiseRelations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Cruise',function(Blueprint $table){

            $table->foreign('sourceId')->references('id')->on('CruiseSource')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('departurePointId')->references('id')->on('CruiseRoutePoint')->onUpdate('CASCADE')->onDelete('RESTRICT');
            $table->foreign('returnPointId')->references('id')->on('CruiseRoutePoint')->onUpdate('CASCADE')->onDelete('RESTRICT');

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
