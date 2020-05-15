<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 24.03.17
 * Time: 15:42
 */

namespace App\Http\Controllers\Api;

use App\Model\ShipCabinCategory as Model;

/**
 * Class ShipCabinCategoryController
 * @package App\Http\Controllers\Api
 */
class ShipCabinCategoryController extends Controller
{
    /**
     * @return array|mixed
     */
    public function index()
    {
        return $this->response(Model::all());
    }

    /**
     * @param $id
     * @return array
     */
    public function getById($id)
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
     * @param $shipId
     * @return array
     */
    public function getByShipId($shipId)
    {
        return $this->response(Model::findAllByShipId($shipId));
    }

    /**
     * @param \App\Model\ShipCabinCategory $item
     * @return array
     */
    protected function renderItemToArray($item)
    {

        $photo = $item->photos()->first();
        return collect(parent::renderItemToArray($item))->forget(['vendorId'])->merge([
            'title' => $item->title,
            'description' => $item->description,
            'photo' => $photo ? $photo->url : null,
            'Ship' => [
                '_meta' => [
                    'loadURI' => $this->action('Ship/id/'.$item->shipId)
                ]
            ],
            'ShipCabinCategoryPhoto' => [
                '_meta' => [
                    'loadURI' => $this->action('ShipCabinCategoryPhoto/categoryId/'.$item->id)
                ]
            ]
        ])->toArray();
    }
}