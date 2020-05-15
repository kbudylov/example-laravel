<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class BookingOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('BookingOrders',function(Blueprint $table){
            $table->bigIncrements('id')->unsigned();
            $table->bigInteger('cruiseId')->unsigned();
            $table->bigInteger('clientId')->unsigned();
            $table->decimal('totalPrice',10,2)->defaultValue(0);
            $table->boolean('status')->defaultValue(1);
            $table->string('payType',32)->nullable();
            $table->bigInteger('dealId')->nullable();
            $table->string('roistat',255)->nullable();
            $table->string('hash',32)->nullable();
            $table->dateTime('hashExpires')->nullable();
            $table->text('comment')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('cruiseId')->references('id')->on('Cruise')->onUpdate('CASCADE')->onDelete('CASCADE');
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
