<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('BookingClients',function(Blueprint $table){
            $table->bigIncrements('id')->unsigned();
            $table->bigInteger('crmId')->nullable();
            $table->string('name',255)->nullable();
            $table->string('firstName',255)->nullable();
            $table->string('lastName',255)->nullable();
            $table->string('surName',255)->nullable();
            $table->string('email',255)->nullable();
            $table->string('phone',255)->nullable();
            $table->timestamps();
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
