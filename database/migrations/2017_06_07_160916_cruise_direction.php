<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CruiseDirection extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('CruiseDirection',function(Blueprint $table){
            $table->bigIncrements('id')->unsigned();
            $table->bigInteger('vendor')->unsigned();
            $table->string('title',255);
            $table->foreign('vendor')->references('id')->on('CruiseSource')->onUpdate('CASCADE')->onDelete('CASCADE');
        });

        Schema::table('Cruise',function(Blueprint $table){
            $table->bigInteger('directionId')->unsigned()->after('shipId')->nullable();
            $table->foreign('directionId')->references('id')->on('CruiseDirection')->onUpdate('CASCADE')->onDelete('SET NULL');
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
