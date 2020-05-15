<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 22.03.17
 * Time: 21:54
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Controller;
use App\Model\PriceVariant as Model;

/**
 * Class PriceVariantController
 * @package App\Http\Controllers\Api
 */
class PriceVariantController extends Controller
{
    /**
     * @var string
     */
    protected $listView = 'api.v1.priceVariant.list';

    /**
     * @var string
     */
    protected $itemView = 'api.v1.priceVariant.item';

    /**
     * @param $format
     * @param $lang
     * @param $cruiseId
     * @return mixed
     */
    public function getByCruiseId($format, $lang, $cruiseId)
    {
        return $this->response(Model::findAllByCruiseId($cruiseId));
    }

    /**
     * @param $format
     * @param $lang
     * @param $cabinId
     * @return array|mixed
     */
    public function getByCruiseCabinId($format, $lang, $cabinId)
    {
        return $this->response(Model::findAllByCruiseCabinId($cabinId));
    }

    /**
     * @param $item Model
     * @return array
     */
    protected function renderItemToArray($item)
    {
        return collect(parent::renderItemToArray($item))->forget(['title'])->merge([])->toArray();
    }
}