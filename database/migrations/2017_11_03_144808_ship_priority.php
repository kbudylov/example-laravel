<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ShipPriority extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Ship',function (Blueprint $table){
        	$table->integer('showPriority')->defaultValue(0);
        });

        DB::update('UPDATE `Ship` SET `showPriority` = 100 WHERE `vendor` = (SELECT `id` FROM `CruiseSource` WHERE `prefix` = ?)',[
        	'volgaline'
        ]);
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
