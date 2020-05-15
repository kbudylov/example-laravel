<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class BookingPassengersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('BookingPassengers',function(Blueprint $table){
            $table->bigIncrements('id')->unsigned();
            $table->bigInteger('orderId')->unsigned();
            $table->bigInteger('cruiseId')->unsigned();
            $table->bigInteger('cabinId')->unsigned();
            //$table->bigInteger('placeId')->unsigned()->nullable();
            $table->bigInteger('categoryId')->unsigned()->defaultValue(1);
            $table->bigInteger('gender')->unsigned()->defaultValue(1);
            $table->string('firstName',255)->nullable();
            $table->string('lastName',255)->nullable();
            $table->string('middleName',255)->nullable();
            $table->date('birthDate')->nullable();
            $table->string('documentNumber',50)->nullable();
            $table->text('phoneNumbers')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('orderId')->references('id')->on('BookingOrders')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('cruiseId')->references('id')->on('Cruise')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('cabinId')->references('id')->on('CruiseCabin')->onUpdate('CASCADE')->onDelete('CASCADE');
            //$table->foreign('placeId')->references('id')->on('CruiseCabinPlace')->onUpdate('CASCADE')->onDelete('CASCADE');
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
