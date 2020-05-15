<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 24.03.17
 * Time: 18:50
 */

namespace App\Request;

use Illuminate\Http\Request;

/**
 * Class APIXMLRequest
 * @package App\Request
 */
class APIXMLRequest extends APIBaseRequest
{
    /**
     * @inheritdoc
     */
    protected function parseRequest(Request $request)
    {
        throw new \RuntimeException('Common XML request parsing is not supported');
    }
}