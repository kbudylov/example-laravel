<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ShipCabinRelations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ShipCabin',function(Blueprint $table){
            $table->foreign('categoryId')->references('id')->on('ShipCabinCategory')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('deckId')->references('id')->on('ShipDeck')->onUpdate('CASCADE')->onDelete('CASCADE');
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
