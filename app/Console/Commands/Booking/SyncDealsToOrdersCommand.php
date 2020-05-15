<?php

namespace App\Console\Commands\Booking;

use App\Components\B24\Entity\Deal;
use App\Model\Booking\Order;
use Illuminate\Console\Command;

class SyncDealsToOrdersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'booking:syncDealsToOrders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $deals = Deal::getList();
        /** @var Deal $deal */
        foreach ($deals as $deal) {
            $dealId = $deal->id;
            $this->info("Processing deal [".$dealId."]");
            if ($order = Order::findByDealId($dealId)) {
                //check status
                $this->info("Found order [$order->id]");
            } else {
                $this->warn("No orders found for deal [$dealId]");
            }
        }
    }
}
