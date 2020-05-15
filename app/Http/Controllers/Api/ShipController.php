<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 22.03.17
 * Time: 21:52
 */

namespace App\Http\Controllers\Api;

use App\Model\Cruise;
use App\Model\Ship as Model;

/**
 * Class ShipController
 * @package App\Http\Controllers\Api
 */
class ShipController extends Controller
{
    /**
     * @return array|mixed
     */
    public function index()
    {
	//select DISTINCT(shipId) from `Cruise`where departureDateTime >= CURDATE() and regionName = "Россия"
        $cruises = Cruise::active(config('import.vendors.infoflot.model.config.shipIdActive',[]))->get();
        $shipIds = [];
        foreach ($cruises as $cruise){
            $shipIds[] = $cruise->shipId;
        }

        $ships = Model::whereIn('id', $shipIds)->orderByDesc('showPriority')->orderBy('title')->get();

        return $this->response($ships);
    }

    /**
     * @param $id
     * @return mixed
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
     * @param Model $item
     * @return array
     */
    protected function renderItemToArray($item)
    {
        return collect(parent::renderItemToArray($item))->forget([
            'vendor','vendorId','services','photoUrl'
        ])->merge([
            'title' => $item->title,
            'services' => $item->services,
            'vendor' => $item->vendorRel->prefix,
            'description' => $item->description,
            'image' => $item->photoUrl,
            'CruiseByShip' => [
                '_meta' => [
                    'loadURI' => $this->action('Cruise/shipId/'.$item->id)
                ]
            ],
            'ShipPhotoByShip' => [
                '_meta' => [
                    'loadURI' => $this->action('ShipPhoto/shipId/'.$item->id)
                ]
            ],
            'ShipCabinByShip' => [
                '_meta' => [
                    'loadURI' => $this->action('ShipCabin/shipId/'.$item->id)
                ]
            ],
            'ShipCabinCategoryByShipId' => [
                '_meta' => [
                    'loadURI' => $this->action('ShipCabinCategory/shipId/'.$item->id)
                ]
            ],
            'ShipDeckByShipId' => [
                '_meta' => [
                    'loadURI' => $this->action('ShipDeck/shipId/'.$item->id)
                ]
            ]
        ])->toArray();
    }
}
