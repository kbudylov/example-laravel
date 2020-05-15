<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 22.03.17
 * Time: 22:54
 */

namespace App\Http\Controllers\Api;

use App\Model\Ship;
use App\Model\ShipCabin;
use App\Model\ShipCabinCategory;
use App\Model\ShipDeck;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


/**
 * Class ShipCabinController
 * @package App\Http\Controllers\Api
 */
class ShipCabinController extends Controller
{
    /**
     * @param $id
     * @return array
     */
    public function getById($id)
    {
        $model = ShipCabin::findById($id);
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
        /** @var Ship $ship */
        $ship = Ship::findOrFail($shipId);
        return $this->response($ship->cabins);
    }

    /**
     * @param $shipId
     * @param $deckId
     * @return array
     */
    public function getByDeckId($shipId, $deckId)
    {
        $ship = Ship::findOrFail($shipId);
        $shipDeck = ShipDeck::findOrFail($deckId);
        if ($shipDeck->shipId == $ship->id) {
            return $this->response($shipDeck->cabins);
        } else {
            throw new NotFoundHttpException('Deck not found for specified ship');
        }
    }

    /**
     * @param $shipId
     * @param $categoryId
     * @return array
     */
    public function getByCategoryId($shipId, $categoryId)
    {
        /** @var Ship $ship */
        $ship = Ship::findOrFail($shipId);
        /** @var ShipCabinCategory $category */
        $category = ShipCabinCategory::findOrFail($categoryId);
        if($category->shipId == $ship->id){
            return $this->response($category->cabins);
        } else {
            throw new NotFoundHttpException('Category not found for specified ship');
        }
    }

    /**
     * @param ShipCabin $item
     * @return array
     */
    protected function renderItemToArray($item)
    {
        return collect(parent::renderItemToArray($item))->forget([
            'type','places','deck','number','vendor','vendorId'
        ])->merge([
            //'type' => $item->type,
            'number' => $item->number,
            'deckId' => $item->deckId,
            'description' => $item->description,
            'amenities' => $item->amenities,
            'Ship' => [
                '_meta' => [
                    'loadURI' => $this->action('Ship/id/'.$item->shipId)
                ]
            ],
            'ShipDeckById' => [
                /*
                'data' => [
                    'title' => $item->deck->title,
                    'index' => $item->deck->index,
                ],
                */
                '_meta' => [
                    'loadURI' => $this->action('ShipDeck/shipId/'.$item->shipId.'/id/'.$item->deckId)
                ]
            ],
            'ShipCabinCategoryById' => [
                '_meta' => [
                    'loadURI' => $this->action('ShipCabinCategory/id/'.$item->categoryId)
                ]
            ],
            'ShipCabinPlacesByShipCabinId' => [
                '_meta' => [
                    'loadURI' => $this->action('ShipCabinPlace/shipCabinId/'.$item->id)
                ]
            ],
            'ShipCabinPhotoByShipCabinId' => [
                'data' => [

                ],
                '_meta' => [
                    'loadURI' => $this->action('ShipCabinPhoto/shipCabinId/'.$item->id)
                ]
            ],
        ])->toArray();
    }
}