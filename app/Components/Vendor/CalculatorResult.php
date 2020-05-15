<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 04.12.17
 * Time: 17:27
 */

namespace App\Components\Vendor;

use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Class CalculatorResult
 * @package App\Components\Vendor
 */
class CalculatorResult
{
    /**
     * @var ParameterBag
     */
    protected $prices;

    /**
     * CalculatorResult constructor.
     */
    public function __construct()
    {
        $this->prices = [
            'CruiseCabins' => [],
            'PriceTotal' => 0.0
        ];
    }

    public function setCabinPassengerCategoryPriceForPlace($cabinId, $categoryId, $price)
    {
        if(!empty($this->prices['CruiseCabins'])){
            foreach ($this->prices['CruiseCabins'] as $k => $cruiseCabinInfo){

                if($cruiseCabinInfo['cruiseCabinId'] == $cabinId){

                    $categoryFound = false;
                    foreach ($this->prices['CruiseCabins'][$k]['Prices'] as $i => $j) {
                        if($j['categoryId'] == $categoryId){
                            $this->prices['CruiseCabins'][$k]['Prices'][$i]['priceForPlace'] = $price;
                            $categoryFound = true;
                        }
                    }
                    if(!$categoryFound){
                        $this->prices['CruiseCabins'][$k]['Prices'][] = [
                            'categoryId' => $categoryId,
                            'priceForPlace' => $price
                        ];
                        $categoryFound = true;
                    }
                }
            }
        } else {
            $this->prices['CruiseCabins'][] = [
                'cruiseCabinId' => $cabinId,
                'Prices' => [
                    [
                        'categoryId' => $categoryId,
                        'priceForPlace' => $price
                    ]
                ]
            ];
        }
    }

    public function setCabinPassengerCategoryPriceTotal($cabinId, $categoryId, $price)
    {
        if(!empty($this->prices['CruiseCabins'])){
            foreach ($this->prices['CruiseCabins'] as $k => $cruiseCabinInfo){

                if($cruiseCabinInfo['cruiseCabinId'] == $cabinId){

                    $categoryFound = false;
                    foreach ($this->prices['CruiseCabins'][$k]['Prices'] as $i => $j) {
                        if($j['categoryId'] == $categoryId){
                            $this->prices['CruiseCabins'][$k]['Prices'][$i]['priceTotal'] = $price;
                            $this->prices['CruiseCabins'][$k]['Prices'][$i]['countPeople'] = !empty($this->prices['CruiseCabins'][$k]['Prices'][$i]['countPeople']) ? $this->prices['CruiseCabins'][$k]['Prices'][$i]['countPeople']++ : 1;
                            $categoryFound = true;
                        }
                    }
                    if(!$categoryFound){
                        $this->prices['CruiseCabins'][$k]['Prices'][] = [
                            'categoryId' => $categoryId,
                            'priceTotal' => $price,
                            'countPeople' => 1
                        ];
                        $categoryFound = true;
                    }
                }
            }
        } else {
            $this->prices['CruiseCabins'][] = [
                'cruiseCabinId' => $cabinId,
                'Prices' => [
                    [
                        'categoryId' => $categoryId,
                        'priceTotal' => $price,
                        'countPeople' => 1
                    ]
                ]
            ];
        }
    }

    public function setTotalPrice($price)
    {
        $this->prices['PriceTotal'] = $price;
    }

    /**
     * @return float
     */
    public function getTotalPrice()
    {
        return $this->prices['PriceTotal'];
    }

    /**
     * @param $cabinId
     * @return float
     */
    public function getCabinPrice($cabinId)
    {
        foreach ($this->prices['CruiseCabins'] as $cabinInfo) {
            if($cabinInfo['cruiseCabinId'] == $cabinId){
                return $cabinInfo;
            }
        }
        return null;
    }

    /**
     * @return array
     */
    public function getCabinsPrices()
    {
        return $this->prices['CruiseCabins'];
    }

    public function getPrices()
    {
        return $this->prices;
    }
}