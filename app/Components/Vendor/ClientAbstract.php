<?php
/**
 * Created by Konstantin Budylov.
 * Mailto: k.budylov@gmail.com
 * Date: 03.11.17 23:44
 **********************************************************************************/

namespace App\Components\Vendor;

use GuzzleHttp\Client;
use Illuminate\Support\Arr;

/**
 * Class Client
 * @package App\Components\Vendor
 */
abstract class ClientAbstract implements ClientInterface
{
	/**
	 * @var string
	 */
	protected $configPath;

	/**
	 * @var Client
	 */
	protected $httpClient;

	/**
	 * @var array
	 */
	protected $config;

	/**
	 * Client constructor.
	 *
	 * @param array $config
	 */
	public function __construct(array $config = [])
	{
		$this->httpClient = new Client();
		$configPath = (!empty($this->configPath) ? $this->configPath : 'import.global').'.client';
		$this->config = config($configPath);
	}

	/**
	 * @param $url
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	protected function executeRequest($url, array $params = [])
	{
		try {
			$url = $this->urlTo($url);
			$params = $this->hydrateParams($params);
			$response = $this->httpClient->request('GET', $url, $params);
			if ($response->getStatusCode() == 200) {
				return \GuzzleHttp\json_decode($response->getBody()->getContents());
			} else {
				throw new \Exception('Request error [' . $response->getStatusCode() . ']');
			}
		} catch (\Exception $e) {
			//todo: report error
			throw $e;
		}
	}

	protected function executeCustomRequest($url, array $params = [])
    {
        try {
            $params = $this->hydrateParams($params);
            $response = $this->httpClient->request('GET', $url, $params);
            if ($response->getStatusCode() == 200) {
                return \GuzzleHttp\json_decode($response->getBody()->getContents());
            } else {
                throw new \Exception('Request error [' . $response->getStatusCode() . ']');
            }
        } catch (\Exception $e) {
            //todo: report error
            throw $e;
        }
    }

	/**
	 * @param $path
	 *
	 * @return string
	 * @throws \Exception
	 */
	protected function urlTo($path)
	{
		if ($this->hasConfig('apiUrl')) {
			return $this->config( 'apiUrl') . '/' . $path;
		} else {
			throw new \Exception('Required API configuration param [apiUrl] is undefined');
		}
	}

	/**
	 * @param string $key
	 * @param null $default
	 *
	 * @return mixed
	 */
	protected function config($key, $default = null)
	{
		return Arr::get($this->config, $key, $default);
	}

	/**
	 * @param $key
	 *
	 * @return bool
	 */
	protected function hasConfig($key)
	{
		return Arr::has($this->config, $key);
	}

	/**
	 * @param array $params
	 *
	 * @return array
	 */
	protected function hydrateParams(array $params)
	{
		return collect($this->config('connect.defaults',[]))->merge($params)->toArray();
	}
}