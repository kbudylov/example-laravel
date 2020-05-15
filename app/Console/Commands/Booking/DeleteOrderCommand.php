<?php

namespace App\Console\Commands\Booking;

use App\Model\Booking\Order;
use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;

class DeleteOrderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'booking:delete {orderId} {--F|force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete booking order by id';

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
     * @throws \Exception
     */
    public function handle()
    {
        $orderId = $this->argument('orderId');
        /** @var Order $order */
        $order = Order::where(['id' => $orderId])->first();
        if ($order) {
            if ($this->option('force')) {
                if ($order->forceDelete()) {
                    $this->info("Order [$orderId] has been completely deleted");
                } else {
                    $this->warn("Order [$orderId] deletion probably failed.");
                }
            } else {
                if ($order->delete()) {
                    $this->info("Order [$orderId] has been trshed");
                } else {
                    $this->warn("Order [$orderId] deletion probably failed.");
                }
            }
        } else {
            $this->error("Order [$orderId] not found");
        }
    }
}
