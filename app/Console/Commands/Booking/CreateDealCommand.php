<?php

namespace App\Console\Commands\Booking;

use App\Jobs\BookingOrderCrmCreateDealJob;
use App\Model\Booking\Order;
use Illuminate\Console\Command;

/**
 * Class CreateDealCommand
 * @package App\Console\Commands
 */
class CreateDealCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'booking:createDeal {orderIds*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create deal for orders';

    /**
     * @var array
     */
    protected $orderIds = [];

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
        $orderIds = $this->argument('orderIds');
        try {
            if (!$orderIds) {
                throw new \RuntimeException("No order IDs specified for deal creation");
            } else if (!\is_array($orderIds)) {
                throw new \RuntimeException("Order IDs: argument is not array");
            } else {
                if (env('APP_DEBUG')) {
                    $this->info('Creating deals for ['.count($orderIds).'] orders:');
                }
                foreach ($orderIds as $orderId) {
                    if (env('APP_DEBUG')) {
                        $this->info('Processing order: ['.$orderId.']');
                    }
                    $order = Order::find($orderId);
                    if ($order) {
                        if (env('APP_DEBUG')) {
                            $this->info('Order ['.$orderId.'] found.');
                        }
                        if (!$order->dealId) {
                            if(config('b24.enableCrmIntegration')){
                                dispatch((new BookingOrderCrmCreateDealJob($order))->onQueue(config('b24.dealCreateQueue','default')));
                                if (env('APP_DEBUG')) {
                                    $this->info('Order ['.$orderId.'] deal jon created.');
                                }
                            } else {
                                $this->warn('Deal creation is disabled due configuration [b24.enableCrmIntegration]');
                            }
                        } else {
                            $this->warn('Order ['.$orderId.'] has attached deal ['.$order->dealId.']. Passing');
                        }
                    } else {
                        $this->warn('Order ['.$orderId.'] not found.');
                    }
                }
            }
        } catch (\Exception $e) {
            $this->error("Exception occurs: " . get_class($e) . "\nin file: " . $e->getFile() . "; on line: " . $e->getLine() . "\nwith message: " . $e->getMessage());
        }
    }
}
