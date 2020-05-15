<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 24.04.17
 * Time: 19:11
 */

namespace App\Components\B24;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * Class HookRequest
 * @package App\Components\B24
 */
class HookRequest
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var string
     */
    protected $eventId;

    /**
     * @var int
     */
    protected $objectId;

    /**
     * @var Collection
     */
    protected $auth;

    /**
     * @var Collection
     */
    protected $data;

    /**
     * @var Collection
     */
    protected $requestData;


    protected $logFilename = __DIR__.'/../../../storage/logs/hook-request.log';

    /**
     * HookRequest constructor.
     * @param Request $request
     * @param Logger|null $logger
     */
    public function __construct(Request $request, Logger $logger)
    {
        $this->logger = $logger;
        if(env('APP_DEBUG')){
            $this->logger->debug('Raw request is: '.print_r($request->all(), 1), []);
        }
        $this->request = $request;
        $this->parseRequest();
    }

    /**
     * @param string $key
     * @return bool
     */
    public function validateKey($key)
    {
        $authKey = $this->auth->get('application_token');
        if($key && $key === $authKey){
            $this->logger->info('Hook authorization success',[$authKey]);
            return true;
        } else {
            $this->logger->error('App key invalid: ['.$authKey.'] is expected, ['.$key.'] is actual',[$authKey,$key]);
            return false;
        }
    }

    /**
     * @return Collection
     */
    public function getAuth()
    {
        return $this->auth;
    }

    /**
     * @return int|null
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * @return string
     */
    public function getEventCode()
    {
        return $this->eventId;
    }


    protected function parseRequest()
    {
        $this->logger->info('Start parsing request',$this->request->all());

        $data = collect($this->request->all());
        $this->auth = collect($data->get('auth',[]));
        $this->eventId = $data->get('event',null);
        $this->data = collect($data->get('data',[]));

        $fields = $this->data->get('FIELDS',null);
        if($fields && isset($fields['ID'])){
            $this->logger->info('Stored object ID: ['.$this->objectId.']');
            $this->objectId = $fields['ID'];
        } else {
            $this->logger->warning('Object ID not found, fields dump is:'.print_r($fields, 1));
        }
        $this->requestData = $data;
    }
}