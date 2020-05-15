<?php

namespace App\Console\Commands\Booking;

use App\Components\B24\Entity\Deal;
use App\Jobs\BookingOrderCrmCreateDealJob;
use App\Model\Booking\Order;
use Illuminate\Console\Command;

/**
 * Syncronize orders to deals
 *
 * Class SyncBookingToDealsCommand
 * @package App\Console\Commands\Booking
 */
class SyncBookingToDealsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'booking:syncOrdersToDeals';

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
     * @throws \Exception
     */
    public function handle()
    {
        try {
            $orders = Order::all();

            $ordersNoDealIds = [];
            $orderInvalidDealIds = [];
            $orderClosedDeals = [];

            /** @var Order $order */
            foreach ($orders as $order) {
                if($order->cruise) {
                    if (empty($order->dealId)) {
                        //no dealId found for order
                        $clientName = iconv("utf-8","windows-1251",$order->client->fullName);
                        $this->warn("Order [$order->id; created: $order->created_at; client:$clientName] has no deal attached.");
                        $ordersNoDealIds[] = $order;
                    } else {
                        //order has deal: check deal
                        $dealId = $order->dealId;
                        $deal = Deal::get($dealId);
                        if (!$deal) {
                            $this->warn("Order [$order->id] has deal id [$order->dealId] but no deal found.");
                            $orderInvalidDealIds[] = $order;
                        } else {
                            //$this->info("Order [$order->id] has deal [$dealId].");
                            $attributes = $deal->getAttributes();
                            //"stageId":"1" -> booking cancelled
                            //"stageId":"LOSE" -> request cancelled
                            //"stageId":"WON" -> sold
                            //"closed":"Y/N"
                            $lose = $attributes['stageId'] == "LOSE";
                            $closed = $attributes['closed'] == 'Y';
                            if ($closed && $lose) {
                                $this->warn("Deal [$dealId] is closed (".$attributes['stageId'].")");
                                $orderClosedDeals[] = $order;
                            }
                        }
                    }
                } else {
                    $order->delete();
                }
            }

            if (!empty($ordersNoDealIds) && $this->confirm("Would you like to create deals for empty - dealId orders?", true)) {
                foreach ( $ordersNoDealIds as $order ) {
                    if ($order->cruise) {
                        $cruiseTitle = iconv("utf-8", "windows-1251", $order->cruise->title);
                        $clientName = iconv("utf-8", "windows-1251", $order->client->fullName);
                        if($this->confirm("Create deal for order [$order->id]: created: $order->created_at; cruise: $cruiseTitle; client: $clientName", true)){
                            dispatch( ( new BookingOrderCrmCreateDealJob( $order ) )->onQueue( config( 'b24.dealCreateQueue', 'default' ) ) );
                            $this->info("Job for order [$order->id] added");
                        } elseif ($this->confirm("Delete order [$order->id]?")) {
                            $order->delete();
                        }
                    } else {
                        $order->delete();
                    }
                }
            }

            if (!empty($orderInvalidDealIds) && $this->confirm("Would you like to process orders with missing deals?")) {
                foreach ($orderInvalidDealIds as $order) {
                    if ($order->cruise) {
                        $deal = Deal::get($order->dealId);
                        if (!$deal) {
                            $cruiseTitle = iconv("utf-8", "windows-1251", $order->cruise->title);
                            $clientName = iconv("utf-8", "windows-1251", $order->client->fullName);
                            if($this->confirm("Delete order [$order->id]: dealId[$order->dealId]; created: $order->created_at; cruise: $cruiseTitle; client: $clientName", false)){
                                $order->delete();
                                $this->info("Order [$order->id] deleted");
                            } else if($this->confirm("Create deal for order [$order->id]: dealId[$order->dealId]; created: $order->created_at; cruise: $cruiseTitle; client: $clientName", false)) {
                                $order->dealId = null;
                                $order->save();
                                dispatch( ( new BookingOrderCrmCreateDealJob( $order ) )->onQueue( config( 'b24.dealCreateQueue', 'default' ) ) );
                                $this->info("Job for order [$order->id] added");
                            }
                        } else {
                            $attributes = $deal->getAttributes();
                            //"stageId":"1" -> booking cancelled
                            //"stageId":"LOSE" -> request cancelled
                            //"stageId":"WON" -> sold
                            //"closed":"Y/N"
                            $lose = $attributes['stageId'] == "LOSE";
                            $closed = $attributes['closed'] == 'Y';
                            if ($closed && $lose) {
                                $this->warn("Deal [$dealId] is closed (".$attributes['stageId'].")");
                                if($this->confirm("Delete order [$order->id]: dealId[$order->dealId]; created: $order->created_at; cruise: $cruiseTitle; client: $clientName", true)){
                                    $order->delete();
                                }
                            }
                        }
                    } else {
                        $order->delete();
                    }
                }
            }

            if (!empty($orderClosedDeals)  && $this->confirm("Would you like to process orders with closed deals?")) {
                foreach ($orderClosedDeals as $order) {
                    if ($order->cruise) {
                        $cruiseTitle = iconv("utf-8", "windows-1251", $order->cruise->title);
                        $clientName = iconv("utf-8", "windows-1251", $order->client->fullName);
                        if($this->confirm("Delete order [$order->id]: dealId[$order->dealId]; created: $order->created_at; cruise: $cruiseTitle; client: $clientName", true)){
                            $order->delete();
                            $this->info("Order [$order->id] deleted");
                        }
                    } else {
                        $order->delete();
                    }
                }
            }
        } catch (\Exception $e) {
            dd($e);
        }
    }
}
