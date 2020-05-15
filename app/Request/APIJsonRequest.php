<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 24.03.17
 * Time: 18:50
 */

namespace App\Request;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Class APIJsonRequest
 * @package App\Request
 */
class APIJsonRequest extends APIBaseRequest
{
    /**
     * @inheritdoc
     * @throws \RuntimeException
     */
    protected function parseRequest(Request $request)
    {
        $body = $request->getContent();
        try {
            $data = json_decode($body);
            if(!is_null($data)) {
                if(is_object($data)){
                    $data = collect($data)->toArray();
                }
                return new ParameterBag($data);
            } else {
                throw new \RuntimeException('data is NULL');
            }
        } catch (\Exception $e) {
            throw new \RuntimeException('Error occurs while parsing json request: '.$e->getMessage());
        }
    }
}