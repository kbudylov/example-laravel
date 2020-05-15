<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVodohodVendor extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('CruiseSource',function (Blueprint $table){
            $table->dropColumn('className');
            $table->dropColumn('config');
            $table->dropColumn('baseUrl');
            $table->dropColumn('helpUrl');
            $table->dropColumn('isActive');
            $table->dropColumn('isEnabled');
            $table->dropColumn('isInvalid');
            $table->dropColumn('isRunning');
            $table->dropColumn('triesCount');
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
