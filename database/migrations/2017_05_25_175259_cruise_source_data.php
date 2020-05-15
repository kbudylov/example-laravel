<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CruiseSourceData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::delete('DELETE FROM `CruiseSource`');

        $sources = [
            [
                'title' => 'Volgaline',
                'prefix' => 'volgaline',
                'config' => json_encode([]),
                'baseUrl' => 'http://api.volgaline.local/v1/json/ru/Cruise',
                'helpUrl' => 'http://api.volgaline.local/v1/help/ru',
                'isActive' => 1,
                'isEnabled' => 1,
                'isRunning' => 0
            ],
            [
                'title' => 'Infoflot',
                'prefix' => 'infoflot',
                'config' => json_encode([]),
                'baseUrl' => 'https://api.infoflot.com/JSON/e8025dcd9ed7e2867ba5321b123eee2483006e8f',
                'helpUrl' => 'https://api.infoflot.com/JSON/Help/',
                'isActive' => 1,
                'isEnabled' => 1,
                'isRunning' => 0
            ]
        ];

        echo "Importing [".count($sources)."] cruise sources into database:";

        foreach ($sources as $source) {
            echo ".";
            DB::table('CruiseSource')->insert($source);
            echo ".";
        }
        echo "Ok\n";
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
