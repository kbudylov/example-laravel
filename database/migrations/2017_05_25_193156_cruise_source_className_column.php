<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CruiseSourceClassNameColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('CruiseSource',function (Blueprint $table){
            $table->string('className')->after('prefix')->notNull();
        });

        DB::update('UPDATE `CruiseSource` SET `className` = ? WHERE `prefix` = \'volgaline\'',[
            \App\Components\Worker\Source\Volgaline::class
        ]);

        DB::update('UPDATE `CruiseSource` SET `className` = ? WHERE `prefix` = \'infoflot\'',[
            \App\Components\Worker\Source\Infoflot::class
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
