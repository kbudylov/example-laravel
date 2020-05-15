<?php

namespace App\Console\Commands;

use App\Components\Vendor\Manager;
use Illuminate\Console\Command;

/**
 * Команда, осуществляющая запуск воркеров для импорта со всех источников данных,
 * в соответствии с конфигурацией
 *
 * @package App\Console\Commands
 */
class ImportAll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:all {vendor?*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform import from all sources';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param Manager $manager
     *
     * @throws \Exception
     */
    public function handle(Manager $manager)
    {
        $vendors = $this->argument('vendor');
        if(!empty($vendors)){
            foreach($vendors as $vendor){
                $manager->importVendor($vendor);
            }
        } else {
            $manager->import();
        }
    }
}
