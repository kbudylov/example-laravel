<?php

namespace App\Http\Controllers\Api;

use App\Model\CruiseDirection;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class CruiseDirectionController
 * @package App\Http\Controllers\Api
 */
class CruiseDirectionController extends Controller
{
    /**
     * @return array
     */
    public function index()
    {
        return $this->response(CruiseDirection::all());
    }

    /**
     * @param Collection $item
     * @return array
     */
    public function renderItemToArray($item)
    {
        return $item->forget([
            'vendor','vendorId','originalName'
        ])->merge([
            'CruiseListByDirectionId' => [
                '_meta' => [
                    '_loadURI' => 'Cruise/directionId/'.$item->id
                ]
            ]
        ])->toArray();
    }
}
