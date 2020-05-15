<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 21.04.17
 * Time: 15:55
 */

namespace App\Components\Validator;

/**
 * Class PhoneInputValidator
 * @package App\Components\Validator
 */
class PhoneInputValidator implements ValidatorInterface
{
    const PHONE_PATTERN = '/^(\+?)([0-9]{1,2})[- ]?([0-9]{3})[- ]?([0-9]{3})[- ]?([0-9]{2})[- ]?([0-9]{2})$/';

    /**
     * @inheritdoc
     */
    public static function validate($value)
    {
        if(preg_match(static::PHONE_PATTERN,$value,$matches)){
            return true;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public static function cleanup($value)
    {
        if(preg_match(static::PHONE_PATTERN,$value,$matches)){
            $countryCode = null;
            $opCode = null;
            $number = null;
            if(!empty($matches[2])) {
                if (empty($matches[1]) && $matches[2] == '8') {
                    $countryCode = '7';
                } else {
                    $countryCode = $matches[2];
                }
                if (!empty($matches[3])) {
                    $opCode = $matches[3];
                }
                if (!empty($matches[4]) && !empty($matches[5]) && !empty($matches[6])) {
                    $number = $matches[4] . $matches[5] . $matches[6];
                }
                return '+' . $countryCode.$opCode.$number;
            }
        }
        return null;
    }
}