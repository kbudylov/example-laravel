<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 22.03.17
 * Time: 21:50
 */

namespace App\Http\Controllers\Api;

use App\Model\CruiseCabin as Model;

/**
 * Class CruiseCabinController
 * @package App\Http\Controllers\Api
 */
class CruiseCabinController extends Controller
{
    /**
     * @return array|mixed
     */
    public function index()
    {
        return $this->response(Model::all());
    }

    /**
     * @param $cabinId
     * @return array
     */
    public function getById($cabinId)
    {
        $model = Model::findById($cabinId);
        if ($model) {
            return $this->response($model);
        } else {
            $this->throwException(404, 'Item not found');
        }
        return $this->response($model);
    }

    /**
     * @param $cruiseId
     * @return array
     */
    public function getByCruiseId($cruiseId)
    {
        return $this->response(Model::findAllByCruiseId($cruiseId));
    }

    /**
     * @param Model $item
     * @return array
     */
    protected function renderItemToArray($item)
    {
        $pricesList = [];
        foreach ($item->prices as $priceVariant) {
            $pricesList[] = [
                'countPeople' => $priceVariant->countPeople,
                'priceForPlace' => $priceVariant->price
            ];
        }

        return [
            "id" => $item->id,
            "vendorId" => $item->vendorId,
            "cruiseId" => $item->cruiseId,
            "isSeparate" => $item->isSeparate,
            "isAvailable" => $item->isAvailable,
            "saleStatusId" => $item->saleStatusId,
            "seatsInCabin" => $item->shipCabin->seatsInCabin,
            "number" => $item->shipCabin->number,
            "categoryId" => $item->shipCabin->categoryId,
            "deckId" => $item->shipCabin->deckId,
            //"type" => $item->shipCabin->type,
            "ShipCabinCategory" => [
                '_meta' => [
                    'loadURI' => $this->action('ShipCabinCategory/id/'.$item->shipCabin->categoryId)
                ]
            ],
            "ShipDeck" => [
                '_meta' => [
                    'loadURI' => $this->action('ShipDeck/id/'.$item->shipCabin->deckId)
                ]
            ],
            "Prices" => $pricesList,
            /*
            'CruiseCabinPlaceByCabin' => [
                '_meta' => [
                    'loadURI' => $this->action('CruiseCabinPlace/cabinId/1'.$item->id)
                ]
            ],
            'SaleStatus' => [
                '_meta' => [
                    'loadURI' => $this->action('SaleStatus/id/'.$item->saleStatusId)
                ]
            ],
            'Separate' => [
                '_meta' => [
                    'loadURI' => $this->action('CabinSeparate/id/'.$item->separateId)
                ]
            ],
            'Cabin' => [
                '_meta' => [
                    'loadURI' => $this->action('ShipCabinController/id/'.$item->cabinId)
                ]
            ],
            'Cruise' => [
                '_meta' => [
                    'loadURI' => $this->action('Cruise/id/'.$item->cruiseId)
                ]
            ],
            'Gender' => [
                '_meta' => [
                    'loadURI' => $this->action('CabinGender/id/'.$item->genderId)
                ]
            ]
            */
        ];
    }
}
