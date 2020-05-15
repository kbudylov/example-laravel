<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 03.03.17
 * Time: 18:46
 */

namespace App\Observers;

use App\Jobs\BookingOrderCrmCreateDealJob;
use App\Model\Booking\Cabin as BookingCabins;
use App\Model\Booking\Cabin;
use App\Model\Booking\Order;
use App\Model\Booking\Passenger as BookingPassenger;
use App\Model\Booking\Passenger;
use App\Model\Client;
use App\Model\CruiseCabin;
use Carbon\Carbon;


/**
 * Class BookingOrderObserver
 * @package App\Observers
 */
class BookingOrderObserver
{
    /**
     * @var Order
     */
    protected $order;

    /**
     * @param Order $order
     */
    public function creating(Order $order)
    {
        $order->beforeSave();
        //Search for existing client for order, if clientId is not set
        if (empty($order->clientId)) {
            $client = $this->getClientForOrder($order);
            $order->clientId = $client->id;
        }
        //order hash params
        $order->hash = $this->generateOrderHash();
        $order->hash_expires = Carbon::now()->addMinutes(Order::HASH_EXPIRES_MINUTES)->toDateTimeString();

        //booking expires at
        $order->expires_at = Carbon::now()->addDay(Order::BOOKING_EXPIRES_DAYS)->toDateTimeString();
    }

    /**
     * @param Order $order
     * @throws \Exception
     */
    public function created(Order $order)
    {
        $this->order = $order;
        $cabins = $this->order->getCabins();
        $passengers = $this->order->getPassengers();
        //creating BookingCabins records
        foreach ($cabins as $cabinId => $cruiseCabin) {
            $this->createBookingCabin($cabinId, $cruiseCabin, !empty($passengers[$cabinId]) ? $passengers[$cabinId] : []);
        }

        //store deal in b24
        if(config('b24.enableCrmIntegration') && empty($this->order->dealId)){
            dispatch((new BookingOrderCrmCreateDealJob($this->order))->onQueue(config('b24.dealCreateQueue','default')));
        }
        $order->afterSave();
    }

    /**
     * @param Order $order
     * @throws \Exception
     */
    public function deleting(Order $order)
    {
        $order->beforeDelete();
        //deleting BookingCabins records
        /** @var Cabin $bookingCabin */
        foreach ($order->cabins as $bookingCabin){
            $bookingCabin->delete();
        }
        /** @var Passenger $bookingPassenger */
        foreach ($order->passengers as $bookingPassenger) {
            $bookingPassenger->delete();
        }
    }

    /**
     *
     */
    public function deleted(Order $order)
    {
        //todo: make good things when order just has been deleted
        $order->afterDelete();
    }

    /**
     * @param Order $order
     */
    public function updating(Order $order)
    {
        $order->beforeUpdate();
        if($order->isDirty('status')){
            if($order->isCancelled()){
                //todo: make something if order has been cancelled
            } elseif($order->isPayed()) {
                /** @var BookingCabins $bookingCabin */
                foreach ($order->cabins as $bookingCabin){
                    //mark all order CruiseCabinPlaces as sold
                    //foreach ($bookingCabin->places as $bookingPlace) {
                    //    /** @var BookingPlaces $bookingPlace */
                    //    $bookingPlace->place->setSold();
                    //}
                }
            }
            //order cabins updated: rebuilding order
            //} elseif(!empty($order->getCabins())) {
            //Search for existing client for order, if clientId is not set
            //    $client = $this->getClientForOrder($order);
            //    $order->clientId = $client->id;
            //    if(!in_array('dealId',$order->getDirty()) && $order->dealId){
            //        $order->queueDealUpdate();
            //    }
        } else if(in_array('dealId',$order->getDirty())){
            //if($order->dealId){
            //    $order->queueDealUpdate();
            //} else {
            //    //store deal in b24
            //    if(config('b24.enableCrmIntegration')){
            //        dispatch((new BookingOrderCrmCreateDealJob($order))->onQueue(config('b24.dealCreateQueue','default')));
            //    }
            //}
        }
    }

    /**
     * @param Order $order
     */
    public function updated(Order $order)
    {
        $this->order = $order;
        //if(!empty($order->getCabins())){
        //    foreach ($order->cabins as $bookingCabin) {
        //        /** @var BookingCabins $bookingCabin */
        //        $bookingCabin->forceDelete();
        //    }
        //    foreach ($order->passengers as $bookingPassenger) {
        //        /** @var BookingPassenger $bookingPassenger */
        //        $bookingPassenger->forceDelete();
        //    }
        //    //creating BookingCabins records
        //    foreach ($order->getCabins() as $cabinId => $cabinOptions) {
        //        $this->createBookingCabin($cabinId, $cabinOptions);
        //    }
        //    //store deal in b24
        //    if(config('b24.enableCrmIntegration') && $order->dealUpdateQueued()){
        //        dispatch((new BookingOrderCrmUpdateDealJob($order))->onQueue(config('b24.dealCreateQueue','default')));
        //    }
        //} else {
        //    //store deal in b24
        //    if(config('b24.enableCrmIntegration') && $order->dealUpdateQueued()){
        //        dispatch((new BookingOrderCrmUpdateDealJob($order))->onQueue(config('b24.dealCreateQueue','default')));
        //    }
        //}
        $order->afterUpdate();
    }

    /**
     * @param Order $order
     */
    public function restored(Order $order)
    {
        //restore all BooingCabins
        //foreach ($order->cabins as $bookingCabin){
        //    /** @var BookingCabins $bookingCabin */
        //    $bookingCabin->restore();
        //}
    }

    /**
     * @param Order $order
     * @return Client
     */
    protected function getClientForOrder(Order $order)
    {
        $credentials = $order->getClientCredentials();
        if (!empty($credentials)) {
            $client = Client::where([
                //'firstName' => $credentials['firstName'],
                //'lastName' => $credentials['lastName'],
                //'surName' => $credentials['surName'],
                'email' => $credentials['email'],
                'phone' => $credentials['phone']
            ])->first();

            if (!$client) {
                $client = Client::create($credentials);
            } else {
                if ($client->crmId) {
                    //todo: check client in b24 (how??)
                }
            }
            return $client;
        } else {
            throw new \RuntimeException('Client credentials are undefined');
        }
    }

    /**
     * @return string
     */
    protected function generateOrderHash()
    {
        return str_random(32);
    }

    /**
     * @param $cabinId
     * @param CruiseCabin $cabin
     * @param array $passengers
     * @return BookingCabins|\Illuminate\Database\Eloquent\Model
     * @throws \Exception
     */
    protected function createBookingCabin($cabinId, CruiseCabin $cabin, array $passengers = [])
    {
        //for each cabin create BookingItem
        /** @var BookingCabins $bookingCabin */
        $bookingCabin = BookingCabins::create([
            'orderId' => $this->order->id,
            'cabinId' => $cabinId,
            //todo: save isSeparate to BookingCabins
            //TODO: get prices for cabin
            'price' => $this->order->getCabinPrice($cabinId)
        ]);
        //Adding BookingPassengers records
        if(!empty($passengers)){
            foreach ($passengers as $passengerOptions) {
                //Adding booking passenger
                $this->createBookingPassenger($bookingCabin->id, $passengerOptions['credentials']);
            }
        }
        return $bookingCabin;
    }

    /**
     * @param $bookingCabinId
     * @param \StdClass $passengerInfo
     * @return BookingPassenger
     */
    protected function createBookingPassenger($bookingCabinId, \StdClass $passengerInfo)
    {
        $attributes = [
            'orderId' => $this->order->id,
            'cruiseId' => $this->order->cruiseId,
            'cabinId' => $bookingCabinId,
            'categoryId' => $passengerInfo->categoryId,
            'gender' => !empty($passengerInfo->gender) ? $passengerInfo->gender : 1,
            'firstName' => !empty($passengerInfo->firstName) ? $passengerInfo->firstName : 'UNDEFINED',
            'lastName' => !empty($passengerInfo->lastName) ? $passengerInfo->lastName : 'UNDEFINED',
            'middleName' => !empty($passengerInfo->middleName) ? $passengerInfo->middleName : 'UNDEFINED',
            'birthDate' => !empty($passengerInfo->birthDate) ? Carbon::parse($passengerInfo->birthDate)->toDateString() : null,
            'documentNumber' => !empty($passengerInfo->documentNumber) ? $passengerInfo->documentNumber : null,
            'documentSeries' => !empty($passengerInfo->documentSeries) ? $passengerInfo->documentSeries : null,
            'phoneNumbers' => json_encode(!empty($passengerInfo->phone) ? $passengerInfo->phone : [])
        ];
        $bookingPassenger = BookingPassenger::create($attributes);
        return $bookingPassenger;
    }
}