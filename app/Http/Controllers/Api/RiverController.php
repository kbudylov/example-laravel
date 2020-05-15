<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 22.03.17
 * Time: 21:53
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Controller;
use \App\Model\River as Model;
/**
 * Class RiverController
 * @package App\Http\Controllers\Api
 */
class RiverController extends Controller
{
    /**
     * @var string
     */
    protected $listView = 'api.v1.river.list';

    /**
     * @var string
     */
    protected $itemView = 'api.v1.river.item';

    /**
     * @return array|mixed
     */
    public function index()
    {
        return $this->response(Model::all());
    }

    /**
     * @param $format
     * @param $lang
     * @param $id
     * @return array|mixed
     */
    public function getById($format, $lang, $id)
    {
        $model = Model::findById($id);
        if ($model) {
            return $this->response($model);
        } else {
            $this->throwException(404, 'Item not found');
        }
        return $this->response($model);
    }

    /**
     * @param $item Model
     * @return array
     */
    protected function renderItemToArray($item)
    {
        return collect(parent::renderItemToArray($item))->merge([
            'title' => $item->title,
            'description' => $item->description,
            'CruiseByRiver' => [
                '_meta' => [
                    'loadURI' => $this->action('CruiseController/riverId/'.$item->id)
                ]
            ]
        ])->toArray();
    }
}