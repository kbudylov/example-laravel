<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class VendorIdFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ShipCabin',function(Blueprint $table){
            $table->string('vendorId',255)->after('shipId');
        });
        Schema::table('ShipCabinCategory',function(Blueprint $table){
            $table->string('vendorId',255)->after('shipId');
        });
        Schema::table('ShipDeck',function(Blueprint $table){
            $table->string('vendorId',255)->after('shipId');
        });
        Schema::table('CruiseCabin',function(Blueprint $table){
            $table->string('vendorId',255)->after('cruiseId');
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
