<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CruiseSourceStatusFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('CruiseSource',function(Blueprint $table){
            $table->timestampTz('availableAt')->nullable();
            $table->timestampTz('startedAt')->nullable();
            $table->timestampTz('completeAt')->nullable();
            $table->tinyInteger('isRunning')->defaultValue(0);
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
