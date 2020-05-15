<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class BookingPlacesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('BookingPlaces',function(Blueprint $table){
            $table->bigIncrements('id')->unsigned();
            $table->bigInteger('orderId')->unsigned();
            $table->bigInteger('orderCabinId')->unsigned();
            $table->bigInteger('placeId')->unsigned();
            $table->bigInteger('categoryId')->unsigned()->defaultValue(1);
            $table->decimal('price',8,2);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('orderId')->references('id')->on('BookingOrders')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('orderCabinId')->references('id')->on('BookingCabins')->onUpdate('CASCADE')->onDelete('CASCADE');
            //$table->foreign('placeId')->references('id')->on('CruiseCabinPlaces')->onUpdate('CASCADE')->onDelete('CASCADE');
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
