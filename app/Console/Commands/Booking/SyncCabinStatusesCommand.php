<?php

namespace App\Console\Commands\Booking;

use App\Model\Booking\Cabin;
use App\Model\Booking\Order;
use App\Model\CruiseCabin;
use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Class SyncCabinStatusesCommand
 * @package App\Console\Commands\Booking
 */
class SyncCabinStatusesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'booking:syncCabinStatuses';

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
        $this->info("Processing booked cabins");
        $orders = Order::whereRaw("deleted_at IS NULL")->get();
        /** @var Order $order */
        foreach ($orders as $order) {
            if($order->deleted_at) {
                $this->info("Order [$order->id] is deleted");
                continue;
            } else {
                if ($order->cruise) {
                    $cruiseTitle = iconv("utf-8", "windows-1251", $order->cruise->title);
                    $cruiseId = $order->cruiseId;
                    /** @var Cabin $bookingCabin */
                    foreach ($order->cabins as $bookingCabin) {
                        $cruiseCabin = $bookingCabin->cabin;
                        if ($cruiseCabin->isAvailable()) {
                            $this->warn("Cruise cabin [$bookingCabin->number] in cruise [$cruiseId] ($cruiseTitle) is available, but present in order [$order->id]");
                            $cruiseCabin->saleStatusId = CruiseCabin::SALE_STATUS_BOOKED;
                            $cruiseCabin->isAvailable = false;
                            $cruiseCabin->save();
                        }
                    }
                } else {
                    $order->delete();
                }
            }
        }

        \DB::update("
            UPDATE CruiseCabin SET saleStatusId = 2, isAvailable = 0 WHERE id IN (
                SELECT cabinId FROM BookingCabins WHERE orderId IN (
                    SELECT id FROM BookingOrders WHERE deleted_at IS NULL
                )
            )
        ");

        //$this->info("Processing available cabins");
        /** @var Builder $q */
        /*
        $results = \DB::select('
                SELECT
                    CC.id, 
                    CC.cruiseId, 
                    CC.saleStatusId, 
                    CC.isAvailable,
                    S.categoryId,
                    BC.orderId
                FROM
                    CruiseCabin CC
                LEFT JOIN BookingCabins BC ON CC.id = BC.cabinId
                LEFT JOIN ShipCabin S ON CC.cabinId = S.id
                WHERE
                    CC.cruiseId IN (
                      SELECT id FROM Cruise WHERE vendor = 1
                    ) AND
                    (CC.isAvailable = 0 OR CC.saleStatusId != 1)
                    AND
                    CC.id NOT IN (
                      SELECT
                        cabinId
                      FROM
                        BookingCabins
                      WHERE orderId IN (
                        SELECT id FROM BookingOrders WHERE deleted_at IS NULL
                      )
                    )
                ');

        if (!empty($results)) {
            $this->warn("Found [".count($results)."] cabins with booked status, but not present in orders.");
            foreach ($results as $row) {
                $cruiseCabin = CruiseCabin::findById($row->id);
                $cruiseCabin -> saleStatusId = 1;
                $cruiseCabin -> isAvailable = 1;
                $cruiseCabin -> save();
            }
        }
        */
    }

    protected function freeOrderCabins($order)
    {

    }
}
