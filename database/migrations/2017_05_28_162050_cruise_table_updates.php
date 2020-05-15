<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CruiseTableUpdates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('CruiseRoutePoint');
        Schema::dropIfExists('CruiseCabin');
        Schema::dropIfExists('Cruise');
        Schema::dropIfExists('City');
        Schema::dropIfExists('River');



        Schema::dropIfExists('GEORiver');
        Schema::dropIfExists('GEOCity');
        Schema::dropIfExists('GEORegion');
        Schema::dropIfExists('GEOCountry');

        Schema::create('GEOCountry',function(Blueprint $table){
            $table->bigIncrements('id')->unsigned();
            $table->string('title',255);
            $table->unique('title');
        });

        DB::insert('INSERT INTO `GEOCountry` (`id`,`title`) VALUES (1, \'Россия\')');

        Schema::create('GEORegion',function(Blueprint $table){
            $table->bigIncrements('id')->unsigned();
            $table->bigInteger('countryId')->unsigned()->defaultValue(1);
            $table->string('title', 255);
            $table->unique(['title']);
            $table->foreign('countryId')->references('id')->on('GEOCountry')->onDelete('CASCADE')->onUpdate('CASCADE');
        });

        Schema::create('GEOCity',function(Blueprint $table){
            $table->bigIncrements('id')->unsigned();
            $table->bigInteger('regionId')->unsigned()->nullable();
            $table->string('title',255);
            $table->unique(['title','regionId']);

            $table->foreign('regionId')->references('id')->on('GEORegion')->onDelete('CASCADE')->onUpdate('CASCADE');
        });

        Schema::create('GEORiver',function(Blueprint $table){
            $table->bigIncrements('id')->unsigned();
            $table->string('title',255);
            $table->unique(['title']);
        });

        Schema::create('CruiseRoutePoint',function(Blueprint $table){
            $table->bigIncrements('id')->unsigned();
            $table->bigInteger('cityId')->unsigned();
            $table->bigInteger('riverId')->unsigned()->nullable();
            $table->integer('index')->defaultValue(0);
            $table->string('title',255)->nullable();
            $table->text('description')->nullable();
        });

        Schema::create('Ship',function(Blueprint $table){
            $table->bigIncrements('id')->unsigned();
            $table->bigInteger('vendor')->unsigned();
            $table->string('vendorId',32);
            $table->string('title',255);
            $table->unique(['vendor','vendorId']);
            $table->unique(['title']);

            $table->foreign('vendor')->references('id')->on('CruiseSource')->onUpdate('CASCADE')->onDelete('CASCADE');
        });

        Schema::create('Cruise',function(Blueprint $table){
            $table->bigIncrements('id')->unsigned();
            $table->bigInteger('vendor')->unsigned();
            $table->string('vendorId',32);
            $table->string('title',255);
            $table->bigInteger('shipId')->unsigned();
            $table->dateTimeTz('departureDatetime');
            $table->dateTimeTz('returnDateTime');
            $table->bigInteger('departurePointId')->unsigned();
            $table->bigInteger('returnPointId')->unsigned();
            $table->bigInteger('riverId')->unsigned();
            $table->tinyInteger('isWeekend')->defaultValue(0);
            $table->tinyInteger('specilOffer')->defaultValue(0);
            $table->text('description')->nullable();
            $table->text('priceInclude')->nullable();
            $table->text('priceNotInclude')->nullable();

            $table->unique(['vendor','vendorId']);

            $table->foreign('vendor')->references('id')->on('CruiseSource')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('shipId')->references('id')->on('Ship')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('departurePointId')->references('id')->on('CruiseRoutePoint')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('returnPointId')->references('id')->on('CruiseRoutePoint')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('riverId')->references('id')->on('GEORiver')->onUpdate('CASCADE')->onDelete('CASCADE');

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
