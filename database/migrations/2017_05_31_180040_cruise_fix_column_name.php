<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CruiseFixColumnName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Cruise',function(Blueprint $table){
            $table->dropColumn('departureDatetime');
        });

        DB::delete('DELETE FROM `Cruise`');

        Schema::table('Cruise',function(Blueprint $table){
            $table->dateTimeTz('departureDateTime')->after('shipId');
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
