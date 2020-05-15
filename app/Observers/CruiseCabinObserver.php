<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 23.03.2018
 * Time: 23:51
 */

namespace App\Observers;
use App\Model\CruiseCabin;
use App\Model\CruiseCabinCategory;

/**
 * Class CruiseCabinObserver
 * @package App\Observers
 */
class CruiseCabinObserver
{
    public function created(CruiseCabin $cabin)
    {
        $cruisecabinCategory = CruiseCabinCategory::where([
            'cruiseId' => $cabin->cruiseId,
            'categoryId' => $cabin->shipCabin->categoryId
        ])->first();
        if (!$cruisecabinCategory) {
            $cruisecabinCategory = new CruiseCabinCategory([
                'cruiseId' => $cabin->cruiseId,
                'categoryId' => $cabin->shipCabin->categoryId
            ]);
        }
        $cruisecabinCategory->calculateCountAvailable();
    }

    public function updated(CruiseCabin $cabin)
    {
        $cruisecabinCategory = CruiseCabinCategory::where([
            'cruiseId' => $cabin->cruiseId,
            'categoryId' => $cabin->shipCabin->categoryId
        ])->first();
        if (!$cruisecabinCategory) {
            $cruisecabinCategory = new CruiseCabinCategory([
                'cruiseId' => $cabin->cruiseId,
                'categoryId' => $cabin->shipCabin->categoryId
            ]);
        }
        $cruisecabinCategory->calculateCountAvailable();
    }
}