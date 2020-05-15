<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 08.04.17
 * Time: 20:38
 */

namespace App\Components\B24\Http;

/**
 * Class Request
 * @package App\Components\B24\Http
 */
class Request
{
    /**
     * @var string
     */
    protected $url;

    /**
     * @var array
     */
    protected $params = [];

    /**
     * @var string
     */
    protected $method = 'POST';

    /**
     * Request constructor.
     * @param $url
     * @param $params
     * @param $method
     */
    public function __construct($url, $params, $method = 'POST')
    {
        $this->url = $url;
        $this->params = $params;
        $this->method = $method;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return '';
    }
}