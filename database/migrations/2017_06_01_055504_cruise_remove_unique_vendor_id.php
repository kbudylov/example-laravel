<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CruiseRemoveUniqueVendorId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Cruise',function (Blueprint $table){
            $table->dropForeign('cruise_vendor_foreign');
            $table->dropUnique('cruise_vendor_vendorid_unique');
        });

        Schema::table('Cruise',function (Blueprint $table){
            $table->foreign('vendor')->references('id')->on('CruiseSource')->onDelete('CASCADE')->onUpdate('CASCADE');
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
