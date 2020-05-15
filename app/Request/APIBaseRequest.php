<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 24.03.17
 * Time: 18:50
 */

namespace App\Request;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Class APIBaseRequest
 * @package App\Request
 */
abstract class APIBaseRequest implements APIRequest
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var ParameterBag
     */
    protected $data;

    /**
     * APIBaseRequest constructor.
     * @param Request $request
     * @param $parse \Closure|null
     */
    public function __construct(Request $request, \Closure $parse = null)
    {
        $this->request = $request;

        $requestBody = $request->getContent();
        $data = [];

        if (!empty($requestBody)) {
            if($parse instanceof \Closure) {
                $data = $parse($request);
            } else {
                $data = $this->parseRequest($request);
            }
        }

        if ($data instanceof ParameterBag) {
            $this->data = $data;
        } else {
            if($data instanceof Collection) {
                $data = $data->toArray();
            } elseif (is_object($data)) {
                $data = (array)$data;
            } elseif(is_scalar($data)) {
                $data = ['param' => $data];
            } else {
                $data = [];
            }
            $this->data = new ParameterBag($data);
        }
    }

    /**
     * @inheritdoc
     */
    public static function load(Request $request, \Closure $parse = null)
    {
        return new static($request, $parse);
    }

    /**
     * @inheritdoc
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    abstract protected function parseRequest(Request $request);
}