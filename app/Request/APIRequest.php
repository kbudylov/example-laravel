<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 24.03.17
 * Time: 18:49
 */

namespace App\Request;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Interface APIRequest
 * @package App\Request
 */
interface APIRequest
{
    /**
     * @param Request $request
     * @param \Closure $parse
     * @return mixed
     */
    public static function load(Request $request, \Closure $parse = null);

    /**
     * @return ParameterBag
     */
    public function getData();
}