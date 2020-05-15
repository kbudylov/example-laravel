<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 24.03.17
 * Time: 17:52
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Controller;
use App\Model\ShipCabinPhoto\ShipCabinPhoto as Model;


/**
 * Class ShipCabinPhotoController
 * @package App\Http\Controllers\Api
 */
class ShipCabinPhotoController extends Controller
{
    /**
     * @var string
     */
    protected $itemView = 'api.v1.shipCabinPhoto.item';

    /**
     * @var string
     */
    protected $listView = 'api.v1.shipCabinPhoto.list';

    /**
     * @param $format
     * @param $lang
     * @param $id
     * @return array|mixed
     */
    public function getById($format, $lang, $id)
    {
        $model = Model::findById($id);
        if ($model) {
            return $this->response($model);
        } else {
            $this->throwException(404, 'Item not found');
        }
        return $this->response($model);
    }

    /**
     * @param $format
     * @param $lang
     * @param $cabinId
     * @return array|mixed
     */
    public function getByShipCabinId($format, $lang, $cabinId)
    {
        return $this->response(Model::findAllByShipCabinId($cabinId));
    }

    /**
     * @param Model $item
     * @return array
     */
    protected function renderItemToArray($item)
    {
        return collect(parent::renderItemToArray($item))->forget([
            'srcType'
        ])->merge([
            'title' => $item->title,
            'path' => $item->url,
            'ShipCabin' => [
                '_meta' => [
                    'loadURI' => $this->action('ShipCabinController/id/'.$item->shipCabinId)
                ]
            ]
        ])->toArray();
    }
}