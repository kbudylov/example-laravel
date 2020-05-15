<?php

namespace App\Console\Commands;

use App\Components\B24\Entity\Deal;
use App\Components\Vendor\Vodohod\Client;
use App\Model\Booking\Order;
use Illuminate\Console\Command;

class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        dd(Order::findByDealId(58267));
    }
}
