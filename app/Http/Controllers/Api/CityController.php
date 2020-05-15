<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 22.03.17
 * Time: 21:54
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Controller;
use App\Model\City;

/**
 * Class Locality
 * @package App\Http\Controllers\Api
 */
class CityController extends Controller
{
    /**
     * @var string
     */
    protected $listView = 'api.v1.locality.list';

    /**
     * @var string
     */
    protected $itemView = 'api.v1.locality.item';

    /**
     * @return array|mixed
     */
    public function index()
    {
        return $this->response(City::all());
    }

    /**
     * @param $format
     * @param $lang
     * @param $id
     * @return array|mixed
     */
    public function getById($format, $lang, $id)
    {
        $model = City::findById($id);
        if ($model) {
            return $this->response($model);
        } else {
            $this->throwException(404, 'Item not found');
        }
        return $this->response($model);
    }

    /**
     * @param $item
     * @return array
     */
    protected function renderItemToArray($item)
    {
        return collect(parent::renderItemToArray($item))->forget([])->merge([
            'title' => $item->title,
            'description' => $item->description,
            'CruiseByPointDeparture' => [
                '_meta' => [
                    'loadURI' => $this->action('CruiseController/pointDepartureId/'.$item->id)
                ]
            ],
            'CruiseByPointReturn' => [
                '_meta' => [
                    'loadURI' => $this->action('CruiseController/pointReturnId/'.$item->id)
                ]
            ],
            'CruiseRouteByLocality' => [
                '_meta' => [
                    'loadURI' => $this->action('CruiseController/localityId/'.$item->id)
                ]
            ]
        ])->toArray();
    }
}