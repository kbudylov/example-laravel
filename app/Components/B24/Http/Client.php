<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 08.04.17
 * Time: 20:38
 */

namespace App\Components\B24\Http;

use App\Exceptions\InvalidConfigException;
use GuzzleHttp\Client as HttpClient;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * Class Client
 * @package App\Components\B24\Http
 */
class Client
{
    /**
     * @var string
     */
    protected $domain;

    /**
     * @var string
     */
    protected $clientId;

    /**
     * @var string
     */
    protected $clientSecret;

    /**
     * @var string
     */
    protected $redirectUrl;

    /**
     * @var mixed
     */
    protected $login;

    /**
     * @var mixed
     */
    protected $password;

    /**
     * @var
     */
    protected $authToken;

    /**
     * @var
     */
    protected $authExpires;


    /**
     * @var static
     */
    private static $instance;

    /**
     * @var string
     */
    protected $logFilename;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * Client constructor.
     * @throws InvalidConfigException
     * @throws \Exception
     */
    public function __construct()
    {
        $domain = config('b24.domain');
        if(!$domain){
            throw new InvalidConfigException('Configuration for B24 domain is missing');
        }

        $clientId = config('b24.clientId');
        if(!$clientId){
            throw new InvalidConfigException('Configuration for B24 clientId is missing');
        }

        $clientSecret = config('b24.clientSecret');
        if(!$clientSecret){
            throw new InvalidConfigException('Configuration for B24 clientSecret is missing');
        }

        $redirectUrl = config('b24.redirectUrl');
        if(!$redirectUrl){
            throw new InvalidConfigException('Configuration for B24 redirectUrl is missing');
        }

        $login = config('b24.login');
        if(!$login){
            throw new InvalidConfigException('Configuration for B24 login is missing');
        }

        $password = config('b24.password');
        if(!$password){
            throw new InvalidConfigException('Configuration for B24 password is missing');
        }

        $this->domain = $domain;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUrl = $redirectUrl;
        $this->login = $login;
        $this->password = $password;

        $this->cookieFilename = __DIR__."/../../../../storage/runtime/b24.cookie.file";

        $this->logFilename = config('b24.requestLog');
        $this->logger = new Logger('B24_REQUEST_LOG');
        $this->logger->pushHandler(new StreamHandler($this->logFilename, Logger::DEBUG));

        $this->deleteCookieFile();
        $this->authorize();
    }

    /**
     * @return Client
     * @throws InvalidConfigException
     * @throws \Exception
     */
    public static function getInstance()
    {
        if(!static::$instance){
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * @return bool
     */
    protected function isAuthorized()
    {
        return !empty($this->authToken) && !$this->isAuthExpired();
    }

    /**
     * @return bool
     */
    protected function isAuthExpired()
    {
        return false;
    }

    /**
     * @param $url
     * @param array $attributes
     *
     * @return mixed|string
     * @throws \Exception
     */
    public function crmQuery($url, $attributes = [])
    {
        if (!$this->isAuthorized()) {
            $this->authorize();
        }
        $attributes = collect($attributes)->merge([
            'auth' => $this->authToken
        ])->toArray();
        return $this->executeRequest("https://{$this->domain}/rest/{$url}",$attributes);
    }

    /**
     * @throws \Exception
     */
    protected function authorize()
    {
        $accessToken = $this->getAccessToken();
        if ($accessToken) {
            $this->authToken = $accessToken;
        } else {
            throw new \RuntimeException("Error: Not set access_token");
        }
    }

    /**
     * @return mixed
     */
    protected function getAccessToken()
    {
        $_url = 'https://'.$this->domain;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $_url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $res = curl_exec($ch);
        $l = '';
        if(preg_match('#Location: (.*)#', $res, $r)) {
            $l = trim($r[1]);
        }
        curl_setopt($ch, CURLOPT_URL, $l);
        $res = curl_exec($ch);
        preg_match('#name="backurl" value="(.*)"#', $res, $math);
        $post = http_build_query([
            'AUTH_FORM' => 'Y',
            'TYPE' => 'AUTH',
            'backurl' => $math[1] ?? '',
            'USER_LOGIN' => $this->login,
            'USER_PASSWORD' => $this->password,
            'USER_REMEMBER' => 'Y'
        ]);
        curl_setopt($ch, CURLOPT_URL, 'https://www.bitrix24.net/auth/');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        $res = curl_exec($ch);
        $l = '';
        if(preg_match('#Location: (.*)#', $res, $r)) {
            $l = trim($r[1]);
        }
        curl_setopt($ch, CURLOPT_URL, $l);
        $res = curl_exec($ch);
        $l = '';
        if(preg_match('#Location: (.*)#', $res, $r)) {
            $l = trim($r[1]);
        }
        curl_setopt($ch, CURLOPT_URL, $l);
        $res = curl_exec($ch);
        curl_setopt($ch, CURLOPT_URL, 'https://'.$this->domain.'/oauth/authorize/?response_type=code&client_id='.$this->clientId);
        $res = curl_exec($ch);
        $l = '';
        if(preg_match('#Location: (.*)#', $res, $r)) {
            $l = trim($r[1]);
        }
        preg_match('/code=(.*)&do/', $l, $code);
        $code = $code[1];
        curl_setopt($ch, CURLOPT_URL, 'https://'.$this->domain.'/oauth/token/?grant_type=authorization_code&client_id='.$this->clientId.'&client_secret='.$this->clientSecret.'&code='.$code.'&scope=crm,user');
        curl_setopt($ch, CURLOPT_HEADER, false);
        $res = curl_exec($ch);
        curl_close($ch);


        $res = json_decode($res);
        if ($res->access_token) {
            return $res->access_token;
        }
        return null;
    }

    /**
     * @param $url
     * @param array $params
     * @param bool $header
     *
     * @return mixed|string
     * @throws \Exception
     */
    protected function executeRequest($url, $params = [], $header = false)
    {
        //$this->logger->info('CRM request',[
        //    'url' => $url,
        //    'params' => $params,
        //    'header' => $header
        //]);

        $url = $url.'?'.http_build_query($params);
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 400);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, $header);

        $this->checkCookieFile();

        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFilename);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookieFilename);

        $result = curl_exec($ch);

        $code = curl_getinfo($ch,CURLINFO_HTTP_CODE);

        //$this->logger->info('CRM response',[
        //    'code' => $code,
        //    'response' => $result
        //]);

        if ($code == 301 || $code == 302) {
            preg_match('/Location:(.*?)\n/',   $result,   $matches);
            $newurl   =   trim(array_pop($matches));
            //$this->logger->info('CRM redirect',[
            //    'code' => $code,
            //    'url' => $newurl
            //]);
            return $newurl;
        }
        curl_close($ch);
        return   $result;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    protected function checkCookieFile()
    {
        if(is_file($this->cookieFilename)){
            if(is_writable($this->cookieFilename)){
                return true;
            } else {
                throw new \Exception('Cookie file ['.$this->cookieFilename.'] is not writable');
            }
        } else {
            if(touch($this->cookieFilename)){
                return true;
            } else {
                throw new \Exception('Cookie file ['.$this->cookieFilename.'] is not writable');
            }
        }
    }

    /**
     *
     */
    protected function deleteCookieFile()
    {
        if(is_file($this->cookieFilename)){
            unlink($this->cookieFilename);
        }
    }
}