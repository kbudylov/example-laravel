<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 20.03.2018
 * Time: 23:27
 */

namespace App\Components\Vendor\Vodohod;

/**
 * Class Facade
 * @package App\Components\Vendor\Vodohod
 */
class Facade extends \Illuminate\Support\Facades\Facade
{
    /**
     * @inheritdoc
     */
    protected static function getFacadeAccessor()
    {
        return 'vodohod';
    }
}