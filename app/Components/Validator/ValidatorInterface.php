<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 21.04.17
 * Time: 15:54
 */

namespace App\Components\Validator;


interface ValidatorInterface
{
    /**
     * @param $value
     * @return boolean
     */
    public static function validate($value);

    /**
     * @param $value
     * @return string
     */
    public static function cleanup($value);
}