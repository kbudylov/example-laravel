<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 03.12.17
 * Time: 1:35
 */

namespace App\Components\Vendor\Infoflot;

use App\Components\Vendor\FactoryAbstract;

/**
 * Class Factory
 * @package App\Components\Vendor\Infoflot
 */
class Factory extends FactoryAbstract
{
    /**
     * @inheritdoc
     */
    public function getApiClient()
    {
        return new Client();
    }

    /**
     * @inheritdoc
     */
    public function getBokingOrderCalculator()
    {
        return new Calculator();
    }
}