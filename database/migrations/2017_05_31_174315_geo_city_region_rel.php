<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GeoCityRegionRel extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('GEOCity',function(Blueprint $table){
            $table->bigInteger('regionId')->unsigned()->nullable();
            $table->foreign('regionId')->references('id')->on('GEORegion')->onUpdate('CASCADE')->onDelete('CASCADE');
        });

        Schema::table('GEORegion',function(Blueprint $table){
            $table->dropForeign('georegion_countryid_foreign');
            $table->dropColumn('countryId');
        });

        Schema::dropIfExists('GEOCountry');
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
