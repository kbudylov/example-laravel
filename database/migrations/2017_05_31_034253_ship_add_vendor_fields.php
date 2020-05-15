<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ShipAddVendorFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::delete('DELETE FROM `Ship`');

        Schema::table('Ship',function(Blueprint $table){
            $table->bigInteger('vendor')->unsigned()->after('id');
            $table->string('vendorId',255)->nullable()->after('vendor');
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
