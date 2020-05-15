<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CruiseRouteRelations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('CruiseRoutePoint',function (Blueprint $table){
            $table->foreign('cruiseId')->references('id')->on('Cruise')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('cityId')->references('id')->on('City')->onUpdate('CASCADE')->onDelete('CASCADE');
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
