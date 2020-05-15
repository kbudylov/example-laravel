<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class BookingCabinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('BookingCabins',function(Blueprint $table){
            $table->bigIncrements('id')->unsigned();
            $table->bigInteger('orderId')->unsigned();
            $table->bigInteger('cabinId')->unsigned();
            $table->decimal('price',8,2)->defaultValue(0);
            $table->foreign('orderId')->references('id')->on('BookingOrders')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('cabinId')->references('id')->on('CruiseCabin')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->unique(['orderId','cabinId']);
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
