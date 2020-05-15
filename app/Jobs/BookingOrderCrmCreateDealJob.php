<?php

namespace App\Jobs;

use App\Components\B24\Service;
use App\Model\Booking\Order as BookingOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * Create CRM deal on booking create
 * @package App\Jobs
 */
class BookingOrderCrmCreateDealJob implements ShouldQueue
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
     * @var Logger
     */
    protected $logger;

    /**
     * @var int
     */
    protected $retries = 0;

    /**
     * BookingOrderCrmCreateDealJob constructor.
     *
     * @param BookingOrder $order
     * @param int $retries
     *
     * @throws \Exception
     */
    public function __construct(BookingOrder $order, $retries = 1)
    {
        $this->retries = $retries;
        $this->order = $order;
        $this->service = new Service();
        $this->logger = new Logger('b24.createDeals.log');
        $this->logger->pushHandler(new StreamHandler(__DIR__.'/../../storage/logs/b24.create.deals.log'));
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->logger->info('Handle deal creation for order ['.$this->order->id.']');
        try {
            $deal = $this->service->crmCreateDeal($this->order);
            if ($deal) {
                $this->logger->info('Deal created success',['id' => $deal->id]);
                $this->order->dealId = $deal->id;
                if($this->order->save()){
                    $this->logger->info('Order ['.$this->order->id.'] save success');
                } else {
                    $this->logger->error('Order ['.$this->order->id.'] save error');
                }
            } else {
                $this->logger->error('Deal create error');
            }
        } catch (\Exception $e) {
            $this->logger->error('Exception occurs while created deal: '.$e->getMessage().'; in file: '.$e->getFile().'; on line: '.$e->getLine());
            if($this->retries <= 3) {
                self::dispatch($this->order, ++$this->retries)->onQueue($this->queue)->delay(60);
            }
        }
    }
}
