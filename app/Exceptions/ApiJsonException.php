<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 28.03.17
 * Time: 21:17
 */

namespace App\Exceptions;

/**
 * Class APIJsonException
 * @package App\Exceptions
 */
class ApiJsonException extends ApiException
{
    /**
     * @inheritdoc
     */
    public function render()
    {
        $response = [
            'status' => $this->getStatusCode(),
            'message' => $this->getMessage(),
            'code' => $this->getExceptionCode()
        ];

        if(env("APP_DEBUG")){
            $e = $this->getPrevious();
            if($e){
                $response['debug'] = [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ];
            } else {
                $response['debug'] = [
                    'code' => $this->getCode(),
                    'file' => $this->getFile(),
                    'line' => $this->getLine()
                ];
            }
        }

        return response()->json($response, $this->getStatusCode());
    }
}