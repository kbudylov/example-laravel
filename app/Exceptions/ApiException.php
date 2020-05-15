<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 28.03.17
 * Time: 21:15
 */

namespace App\Exceptions;

use \Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class APIException
 * @package App\Exceptions
 */
abstract class ApiException extends HttpException
{
    const CODE_SERVER_ERROR = 'SERVER_ERROR';

    const CODE_INVALID_PARAM = 'INVALID_PARAM';

    const CODE_RESOURCE_UNAVAILABLE = 'RESOURCE_UNAVAILABLE';

    protected $exceptionCode;

    /** ApiException constructor.
     * @param string $apiExceptionCode
     * @param null $message
     * @param \Exception $statusCode
     * @param \Exception|null $previous
     * @param array $headers
     * @param int $code
     */
    public function __construct($apiExceptionCode, $message = null, $statusCode, \Exception $previous = null, array $headers = array(), $code = 0)
    {
        $this->exceptionCode = $apiExceptionCode;
        parent::__construct($statusCode, $message, $previous, $headers, $code);
    }

    /**
     * @return string
     */
    public function getExceptionCode()
    {
        return $this->exceptionCode;
    }

    /**
     * @return mixed
     */
    abstract public function render();
}