<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\BadRequestException;
use App\Model\Booking\Order;
use App\Model\Client;
use App\Model\Cruise;
use App\Model\CruiseCabin;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


/**
 * Class BookingController
 * @package App\Http\Controllers\Api
 */
class BookingController extends Controller
{
    /**
     * @var bool
     */
    protected $useCabinVendorId = false;

    /**
     * @param $cruiseId
     * @return array
     * @throws BadRequestException
     * @throws \Exception
     */
    public function bookingPrice($cruiseId)
    {
        try {

            $params = $this->request();

            /** @var \App\Model\Cruise $cruise */
            $cruise = Cruise::findById($cruiseId);

            if ($cruise) {

                $order  = $cruise->createBookingOrder();

                //Adding cabins to order
                foreach ($params->all() as $k => $cabinOptions) {
                    if (isset($cabinOptions->cruiseCabin)) {

                        $cruiseCabin = $this->findCruiseCabin($cabinOptions->cruiseCabin);

                        $passengersList = [];
                        foreach ($cabinOptions->priceCategory as $categoryInfo) {
                            for($i = 0; $i < $categoryInfo->count; $i++){
                                $passengersList[] = [
                                    'category' => $categoryInfo->id,
                                    'credentials' => null
                                ];
                            }
                        }

                        $order->addCabinById($cruiseCabin->id, $passengersList);

                    } else {
                        throw new BadRequestHttpException("Undefined [cruiseCabin] in the parameter list item [$k]");
                    }
                }

                $order->calculatePrice();
                $response = [
                    "CruiseCabins" => $order->getCabinPrices(),
                    'PriceTotal' => $order->totalPrice
                ];

                if($this->useCabinVendorId){
                    foreach ($response['CruiseCabins'] as $k => $cruiseCabinInfo){
                        $response['CruiseCabins'][$k]['cruiseCabinId'] = CruiseCabin::findById($response['CruiseCabins'][$k]['cruiseCabinId'])->vendorId;
                    }
                }

                return $response;

            } else {
                throw new NotFoundHttpException('Cruise ['.$cruiseId.'] not found');
            }

        } catch(\Exception $e) {
            $response = [
                'success' => false,
                'message' => $e->getMessage()
            ];
            if(env('APP_DEBUG')){
                $response['file'] = $e->getFile();
                $response['line'] = $e->getLine();
                $response['trace'] = $e->getTrace();
            }
            return $response;
        }
    }

    /**
     * @param $cruiseId
     * @return array
     */
    public function booking($cruiseId)
    {
        try {
            $params = $this->request();

            /** @var \App\Model\Cruise $cruise */
            $cruise = Cruise::findById($cruiseId);

            if ($cruise) {

                $order  = $cruise->createBookingOrder([
                    'payType' => $params->get('client')->payType,
                ]);

                $order->setClientCredentials([
                    'name' => $params->get('client')->name,
                    'email' => $params->get('client')->email,
                    'phone' => $params->get('client')->phone
                ]);

                //Adding cabins to order
                foreach ($params->get('cabins') as $k => $cabinOptions) {
                    if (isset($cabinOptions->cruiseCabin)) {

                        $cruiseCabin = $this->findCruiseCabin($cabinOptions->cruiseCabin);

                        $passengersList = [];

                        foreach ($cabinOptions->passengers as $passengerInfo) {

                            $passengersList[] = [
                                'category' => !empty($passengerInfo->categoryId) ? $passengerInfo->categoryId : Order::DEFAULT_PASSENGER_CATEGORY,
                                'credentials' => $passengerInfo
                            ];
                        }

                        $order->addCabinById($cruiseCabin->id, $passengersList);

                    } else {
                        throw new BadRequestHttpException("Undefined [cruiseCabin] in the parameter list item [$k]");
                    }
                }

                $order->calculatePrice();

                if($order->save()){
                    return [
                        'status' => 200,
                        'message' => 'Ok',
                        'id' => $order->hash,
                        'expires' => $order->hash_expires
                    ];
                } else {
                    throw new \Exception('Order has not been saved');
                }

            } else {
                throw new NotFoundHttpException('Cruise ['.$cruiseId.'] not found');
            }
        } catch (\Exception $e ) {
            $response = [
                'success' => false,
                'message' => $e->getMessage()
            ];
            if(env('APP_DEBUG')){
                $response['file'] = $e->getFile();
                $response['line'] = $e->getLine();
                $response['trace'] = $e->getTrace();
            }
            return $response;
        }
    }

    /**
     * @param $id
     * @return CruiseCabin
     */
    protected function findCruiseCabin($id)
    {
        $cabin = CruiseCabin::where([
            'vendorId' => $id
        ])->first();
        if (!$cabin) {
            $this->useCabinVendorId = false;
            $cabin = CruiseCabin::findById($id);
        } else {
            $this->useCabinVendorId = true;
        }
        if (!$cabin) {
            throw new NotFoundHttpException("Cruise cabin [$id] not found");
        }

        return $cabin;
    }
}
