<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class BookingPassengersRelationsFix extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('BookingPassengers',function (Blueprint $table){
            $table->dropForeign('bookingpassengers_cabinid_foreign');
        });

        Schema::table('BookingPassengers',function (Blueprint $table){
            $table->foreign('cabinId')->references('id')->on('BookingCabins')->onUpdate('CASCADE')->onDelete('CASCADE');
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
