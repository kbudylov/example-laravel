<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CruiseRoutePointUpdates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('CruiseRoutePoint',function(Blueprint $table){
            $table->dropColumn('index');
            $table->dropColumn('arrivalDateTime');
            $table->dropColumn('departureDateTime');
            $table->dropColumn('riverId');
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
