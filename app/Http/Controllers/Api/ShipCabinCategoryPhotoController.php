<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 24.03.17
 * Time: 18:19
 */

namespace App\Http\Controllers\Api;

use App\Model\ShipCabinCategoryPhoto;

/**
 * Class ShipCabinCategoryPhoto
 * @package App\Http\Controllers\Api
 */
class ShipCabinCategoryPhotoController extends Controller
{
    /**
     * @param $categoryId
     * @return array
     */
    public function getAllByCategoryId($categoryId)
    {
        return $this->response(ShipCabinCategoryPhoto::findAllByCategoryId($categoryId));
    }

    /**
     * @param $item
     * @return array
     */
    protected function renderItemToArray($item)
    {
        return collect(parent::renderItemToArray($item))->forget([
            'id',
        ])->merge([
            'categoryId' => $item->categoryId,
            'url' => $item->url
        ])->toArray();
    }
}