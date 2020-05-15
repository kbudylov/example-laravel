<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 08.04.17
 * Time: 20:39
 */

namespace App\Components\B24\Exception;

use Throwable;

/**
 * Class HttpException
 * @package App\Components\B24\Exception
 */
class HttpException extends \HttpException
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}