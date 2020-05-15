<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 09.04.17
 * Time: 0:04
 */

namespace App\Http\Controllers\Api;

use App\Model\Cruise\Cruise as Model;

/**
 * Class CruiseControllerSearch
 * @package App\Http\Controllers\Api
 */
class CruiseSearchController extends CruiseController
{
    public function renderItemToArray($item)
    {
        $routes = $item->route->pluck('id');
        $item = collect(parent::renderItemToArray($item))->merge([
            'routeIds' => $routes
        ])->toArray();
        return $item;
    }
}