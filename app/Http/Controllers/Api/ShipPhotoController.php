<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 24.03.17
 * Time: 16:32
 */

namespace App\Http\Controllers\Api;

use App\Model\ShipPhoto;

/**
 * Class ShipPhotoController
 * @package App\Http\Controllers\Api
 */
class ShipPhotoController extends Controller
{
    /**
     * @param $id
     * @return array|mixed
     */
    public function getById($id)
    {
        $model = ShipPhoto::findById($id);
        if ($model) {
            return $this->response($model);
        } else {
            $this->throwException(404, 'Item not found');
        }
        return $this->response($model);
    }

    /**
     * @param $shipId
     * @return array|mixed
     */
    public function getByShipId($shipId)
    {
        return $this->response(ShipPhoto::findAllByShipId($shipId));
    }

    /**
     * @param ShipPhoto $item
     * @return array
     */
    protected function renderItemToArray($item)
    {
        return collect(parent::renderItemToArray($item))->forget([
            'thumbUrl','imageUrl','id'
        ])->merge([
            'shipId' => $item->shipId,
            'thumbUrl' => $item->thumbUrl,
            'imageUrl' => $item->imageUrl,
            'Ship' => [
                '_meta' => [
                    'loadURI' => $this->action('Ship/id/'.$item->shipId)
                ]
            ]
        ])->toArray();
    }
}