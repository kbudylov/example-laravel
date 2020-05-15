<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 08.04.17
 * Time: 20:39
 */

namespace App\Components\B24\Http;

use \GuzzleHttp\Psr7\Response as HttpResponse;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Class Response
 * @package App\Components\B24\Http
 */
class Response
{
    /**
     * @var HttpResponse
     */
    protected $response;

    /**
     * @var ParameterBag
     */
    protected $params;

    /**
     * Response constructor.
     * @param HttpResponse $response
     */
    public function __construct(HttpResponse $response)
    {
        $this->response = $response;
        $this->params = $this->parseResponse($response);
    }

    /**
     * @return bool
     */
    public function isOk()
    {
        return $this->response->getStatusCode() === 200;
    }

    /**
     * @return ParameterBag
     */
    public function params()
    {
        return $this->params;
    }

    /**
     * @return \GuzzleHttp\Psr7\Stream|\Psr\Http\Message\StreamInterface
     */
    public function getBody()
    {
        return $this->response->getBody();
    }

    /**
     * @return HttpResponse
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->response->getStatusCode();
    }

    /**
     * @return string|null
     */
    public function getError()
    {
        return $this->params->get('error_message');
    }

    /**
     * @param \GuzzleHttp\Psr7\Response $response
     * @return ParameterBag
     */
    protected function parseResponse(HttpResponse $response)
    {
        $params = [];
        $contents = $response->getBody()->getContents();
        preg_match_all('/\'([a-z_]+)\':\'([^\']+)\'/i',$contents,$matches, PREG_SET_ORDER);
        foreach ($matches as $match){
            $params[$match[1]] = $match[2];
        }
        return new ParameterBag($params);
    }
}