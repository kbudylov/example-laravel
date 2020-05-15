<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CruiseRoutePointDates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('CruiseRoutePoint',function(Blueprint $table){
            $table->dateTimeTz('departureDateTime')->nullable()->after('index');
            $table->dateTimeTz('arrivalDateTime')->nullable()->after('index');
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
