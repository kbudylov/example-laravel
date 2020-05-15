<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 03.04.17
 * Time: 20:24
 */

namespace App\Http\Controllers\Api;

use App\Model\ShipDeck;

/**
 * Class ShipDeckController
 * @package App\Http\Controllers\Api
 */
class ShipDeckController extends Controller
{
    /**
     * @param $id
     * @return array
     */
    public function getById($id)
    {
        return $this->response(ShipDeck::findById($id));
    }

    /**
     * @param $shipId
     * @return array
     */
    public function getByShipId($shipId)
    {
        return $this->response(ShipDeck::findAllByShipId($shipId));
    }

    /**
     * @param Model $item
     * @return array
     */
    protected function renderItemToArray($item)
    {
        return collect(parent::renderItemToArray($item))->forget([
            'vendorId'
        ])->merge([
            'title' => $item->title,
            'index' => $item->index,
            'schemeUrl' => $item->schemeUrl,
        ])->toArray();
    }
}