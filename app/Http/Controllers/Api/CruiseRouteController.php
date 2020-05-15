<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 22.03.17
 * Time: 21:48
 */

namespace App\Http\Controllers\Api;

use App\Model\CruiseRoute;


/**
 * Class CruiseRouteController
 * @package App\Http\Controllers\Api
 */
class CruiseRouteController extends Controller
{
    /**
     * @param $cruiseId
     * @return array
     */
    public function getByCruiseId($cruiseId)
    {
        return $this->response(CruiseRoute::findAllByCruiseId($cruiseId));
    }

    /**
     * @param \App\Model\CruiseRoute $item
     * @return array
     */
    protected function renderItemToArray($item)
    {
        /** @var CruiseRoute $item */
        return collect(parent::renderItemToArray($item))->forget([
            'pointId','id'
        ])->merge([
            "index" => $item->index,
            "title" => $item->point->city->title,
            "localityId" => $item->point->cityId,
            "description" => $item->description,
            "Cruise" => [
                '_meta' => [
                    'loadURI' => $this->action('Cruise/id/'.$item->cruiseId)
                ]
            ],
            "City" => [
                '_meta' => [
                    'loadURI' => $this->action('City/id/'.$item->point->cityId)
                ]
            ]
        ])->toArray();
    }
}