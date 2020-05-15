<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ShipCabinRemoveUniqueCabinNumberIdx extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ShipCabin',function(Blueprint $table){
            $table->dropForeign('shipcabin_shipid_foreign');
            $table->dropUnique('shipcabin_shipid_number_unique');
        });
        Schema::table('ShipCabin',function(Blueprint $table){
            $table->foreign('shipId')->references('id')->on('Ship')->onUpdate('CASCADE')->onDelete('CASCADE');
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
