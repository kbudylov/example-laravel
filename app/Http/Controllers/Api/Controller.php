<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 20.03.17
 * Time: 22:51
 */

namespace App\Http\Controllers\Api;

use App\Exceptions\ApiJsonException;
use App\Request\APIJsonRequest;
use App\Request\APIRequest;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class Controller
 * @package App\Http\Controllers\Api
 */
class Controller extends BaseController
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var int
     */
    protected $limit = 100;

    /**
     * @var int
     */
    protected $offset = 0;


    /** @var  ParameterBag */
    protected $requestParsed;

    /**
     * Controller constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->requestParsed = $this->request();
        $this->limit = $request->get('limit', $this->requestParsed->get('limit',100));
        $this->offset = $request->get('offset', $this->requestParsed->get('offset',0));
    }

    /**
     * @param Collection $items
     * @return array
     */
    protected function renderListToArray(Collection $items)
    {
        $jsonData = [
            'total' => $items->count(),
            'data' => [],
            'limit' => (int)$this->limit,
            'offset' => (int)$this->offset
        ];
        $items = $items->slice($this->offset, $this->limit);
        foreach ($items as $item) {
            $jsonData['data'][] = $this->renderItemToArray($item);
        }
        return $jsonData;
    }

    /**
     * @param $item
     * @return array
     */
    protected function renderItemToArray($item)
    {
        if ($item instanceof Model) {
           $item = collect($item->toArray());
        } elseif(is_array($item)) {
            $item = collect($item);
        } else {
            $item = collect((array)$item);
        }
        return $item->forget([
            'created_at','updated_at','deleted_at'
        ])->toArray();
    }

    /**
     * @param $url
     * @return string
     */
    protected function action($url = null)
    {
        return 'http://'.env('API_HOSTNAME').'/api/'.$url;
    }

    /**
     * @return ParameterBag
     */
    protected function request()
    {
        return $this->parseRequest();
    }

    /**
     * @param $data
     * @return array
     */
    protected function response($data)
    {
        if ($data instanceof Collection) {
            return $this->renderListToArray($data);
        } elseif($data instanceof Model || is_array($data) || is_object($data)) {
            return $this->renderItemToArray($data);
        } elseif(is_null($data)) {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @return ParameterBag
     */
    private function parseRequest()
    {
        if (!$this->requestParsed) {
            $requestBody = json_decode($this->request->getContent());
            $this->requestParsed = new ParameterBag((array)$requestBody);
        }
        return $this->requestParsed;
    }

    /**
     * @param $exceptionCode
     * @param $message
     * @param int $httpStatus
     * @param \Exception|null $previous
     */
    protected function throwException($exceptionCode, $message, $httpStatus = 500, \Exception $previous = null)
    {
        throw new ApiJsonException($exceptionCode, $message, $httpStatus, $previous);
    }

    /**
     * @return bool
     */
    protected function isCacheExists($cacheName)
    {

    }

    /**
     * @param $cacheName
     * @return mixed
     */
    protected function getCached($cacheName)
    {

    }

    /**
     * @param $cacheName
     * @param $data
     */
    protected function setCached($cacheName, $data)
    {

    }
}