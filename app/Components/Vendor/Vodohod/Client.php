<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 20.03.2018
 * Time: 23:19
 */

namespace App\Components\Vendor\Vodohod;

use App\Components\Vendor\ClientAbstract;
use GuzzleHttp\Exception\ClientException;

/**
 * Class Client
 * @package App\Components\Vendor\Vodohod
 */
class Client extends ClientAbstract
{
    /**
     * @var string
     */
    protected $configPath = 'import.vendors.vodohod';

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getCruiseList()
    {
        return $this->executeRequest('cruises');
    }

    /**
     * @param $id
     *
     * @return mixed
     * @throws \Exception
     */
    public function getCruiseInfo($id)
    {
        return $this->executeCustomRequest("https://www.rech-agent.ru/api/v1/cruise/$id");
    }
}