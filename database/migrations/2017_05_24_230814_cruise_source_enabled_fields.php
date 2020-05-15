<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CruiseSourceEnabledFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('CruiseSource',function(Blueprint $table){
            $table->dropColumn('availableAt');
            $table->dropColumn('startedAt');
            $table->dropColumn('completeAt');
            $table->tinyInteger('isEnabled')->default(1)->after('isActive');
            $table->tinyInteger('triesCount')->default(0)->before('isRunning');
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
