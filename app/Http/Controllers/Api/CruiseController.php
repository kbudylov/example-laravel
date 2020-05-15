<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 20.03.17
 * Time: 22:51
 */

namespace App\Http\Controllers\Api;

use App\Components\Vendor\Manager;
use App\Model\Cruise;
use App\Model\CruiseRoute;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Class CruiseController
 * @package App\Http\Controllers\Api
 */
class CruiseController extends Controller
{
    /**
     * @return array|mixed
     */
    public function index()
    {
        $order = $this->request()->get('order',[]);
        $column = !empty($order[0]) ? $order[0] : null;
        $asc = !empty($order[1]) ? $order[1] : null;
        return $this->response(Cruise::findAll($column, $order));
    }

    /**
     * @param $id
     * @return array
     */
    public function getById($id)
    {
        $model = Cruise::findById($id);
        if ($model) {
            return $this->response($model);
        } else {
            $this->throwException(404, 'Item not found');
        }
    }

    /**
     * @param $vendor
     * @return array|mixed
     */
    public function getByVendorId($vendor)
    {
        return $this->response(Cruise::findAllByVendorId($vendor));
    }

    /**
     * @param $directionId
     * @return array
     */
    public function getByDirectionId($directionId)
    {
        return $this->response(Cruise::findAllBuDirectionId($directionId));
    }

    /**
     * @param $shipId
     * @return array
     */
    public function getByShipId($shipId)
    {
        return $this->response(Cruise::findAllByShipId($shipId));
    }

    /**
     * @return array
     */
    public function search()
    {
        try {
            $params = new ParameterBag((array)$this->request()->get('search',[]));
            $order = $this->request()->get('order',[]);

            return $this->response(Cruise::search($params, !empty($order[0]) ? $order[0] : null, !empty($order[1]) ? $order[1] : false));
        } catch (\Exception $e) {
            $this->throwException(500, $e->getMessage());
        }
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function searchFields()
    {
        $params = $this->request();
        return Cruise::searchFields($params, !empty($order[0]) ? $order[0] : null, !empty($order[1]) ? $order[1] : false);
    }

    /**
     * Обновление всех данных круизов volgaline
     *
     * @param Manager $manager
     * @throws \Exception
     */
    public function updateAll(Manager $manager)
    {
        $result = $manager->importVendor('volgaline');
        if ($result) {
            exit('Ok');
        } else {
            exit('Something is wrong');
        }
    }

    /**
     * @param Cruise $item
     * @param Request|null $request
     * @return array
     */
    protected function renderItemToArray($item, Request $request = null)
    {
        /** @var  Cruise $item */
        $routeList = [];
        /** @var CruiseRoute $cruiseRouteItem */
        foreach ($item->route as $cruiseRouteItem){
            $routeList[] = [
                'id' => $cruiseRouteItem->point->cityId,
                'title' => $cruiseRouteItem->point->city->title,
            ];
        }

        $cruiseImageUrl = $item->direction->photoUrl ? $item->direction->photoUrl : $item->ship->photoUrl ? $item->ship->photoUrl : null;

        $cabinCategoriesAvailable = [];
        $totalCabinsCount = 0;
        foreach ($item->cabinCategories as $cabinCategory) {
            $cabinCategoriesAvailable[$cabinCategory->categoryId] = $cabinCategory->countAvailable;
            $totalCabinsCount += $cabinCategory->countAvailable;
        }

        return collect(parent::renderItemToArray($item))->forget([
            'isAvailable','vendorId','vendor','departurePointId',
            'returnPointId','directionId','regionName','info',
            'bookingPriceUrl','bookingUrl'
        ])->merge([
            'title' => $item->title,
            'shipName' => $item->ship->title,
            'shipId' => $item->shipId,
            'route' => $item->direction ? $item->direction->title : null,
            'routePhotoUrl' => $item->direction ? $item->direction->photoUrl : null,
            'cruisePhotoUrl' => $cruiseImageUrl,
            'vendor' => $item->vendorRel->prefix,
            'vendorId' => $item->vendorId,
            'description' => $item->description,
            'priceInclude' => $item->priceInclude,
            'priceNotInclude' => $item->priceNotInclude,
            'pointDepartureTitle' => $item->returnPointTitle,
            'pointReturnTitle' => $item->returnPointTitle,
            'countAvailable' => [
                'total' => $totalCabinsCount,
                'category' => $cabinCategoriesAvailable
            ],
            'CruiseRoute' => [
                '_list' => $routeList,
                '_meta' => [
                    "loadURI" => $this->action("CruiseRoute/cruiseId/".$item->id)
                ]
            ],
            'Ship' => [
                '_meta' => [
                    "loadURI" => $this->action("Ship/id/".$item->shipId)
                ]
            ],
            "CruiseCabin" => [
                '_meta' => [
                    'loadURI' => $this->action('CruiseCabin/cruiseId/'.$item->id)
                ]
            ],
            "BookingPrice" => [
                '_meta' => [
                    'loadURI' => $this->action('Cruise/'.$item->id.'/bookingPrice')
                ]
            ],
            "Booking" => [
                '_meta' => [
                    'loadURI' => $this->action('Cruise/'.$item->id.'/booking')
                ]
            ]
        ])->toArray();
    }
}
