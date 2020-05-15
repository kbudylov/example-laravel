<?php

namespace App\Http\Controllers\Api;

use App\Model\ShipCabin;

class ShipCabinPlaceController extends Controller
{
    /**
     * @param $shipCabinId
     * @return array
     */
    public function getByShipCabinId($shipCabinId)
    {
        /** @var ShipCabin\ $shipCabin */
        $shipCabin = ShipCabin::findOrFail($shipCabinId);
        return $this->response($shipCabin->places);
    }

    /**
     * @param $item
     * @return array
     */
    protected function renderItemToArray($item)
    {
        return collect(parent::renderItemToArray($item))->forget([
            'vendor','vendorId'
        ])->merge([
            'id' => $item->id,
            'title' => $item->title,
            'type' => $item->type,
            'position' => $item->position,
        ])->toArray();
    }
}
