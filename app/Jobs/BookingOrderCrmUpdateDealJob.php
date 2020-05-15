<?php

namespace App\Jobs;

use App\Components\B24\Service;
use App\Model\Booking\Order as BookingOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class BookingOrderCrmUpdateDealJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var BookingOrder
     */
    protected $order;

    /**
     * @var Service
     */
    protected $service;

    /**
     * BookingOrderCrmUpdateDealJob constructor.
     * @param BookingOrder $order
     */
    public function __construct(BookingOrder $order)
    {
        $this->order = $order;
        $this->service = new Service();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if($this->order->dealId){
            $this->service->crmUpdateDeal($this->order);
        }
    }
}
