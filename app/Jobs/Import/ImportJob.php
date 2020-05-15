<?php

namespace App\Jobs\Import;

use App\Components\Vendor\ClientInterface;
use App\Model\CruiseSource as Vendor;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * Class ImportJob
 * @package App\Jobs\Import
 */
abstract class ImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	/**
	 * Path to current job configuration (e.g. config('path.to.job.config'))
	 * Default: null
	 * @var string
	 */
    protected $configPath;

	/**
	 * Job logger
	 * @var Logger
	 */
    protected $logger;

    /**
    **/
    protected $loggerName;

    /**
    **/
    protected $logFilename;

    /**
    **/
    protected $logLevel;

	/**
	 * @var Vendor
	 */
    protected $vendor;

	/**
	 * ImportJob constructor.
	 */
	public function __construct()
	{
    $loggerName = !empty($this->loggerName) ? $this->loggerName : $this->config('logger.name',static::class.':logger');
    $logFilename = !empty($this->logFilename) ? $this->logFilename : $this->config('logger.logFilename');
    $logLevel = !empty($this->logLevel) ? $this->logLevel : $this->config('logger.logLevel');
    
		$this->logger = new Logger($loggerName);
		$this->logger->pushHandler(new StreamHandler($logFilename,$logLevel));

		//checking is job still running
		if ($this->isJobRunning()){
			$this->release();
			return;
		}

    $prefix = $this->config('prefix',null);
    if($prefix){
      $vendor = Vendor::findByPrefix($prefix);
      if(!$vendor){
        throw new \RuntimeException('Vendor ['.$prefix.'] not found in the database.');
      }
      $this->vendor = $vendor;
    } else {
      throw new \RuntimeException('Vendor prefix ['.$this->configPath.'.prefix'.'] is undefined in configuration.');
    }
	}

	/**
	 * @param string $message
	 * @param array $context
	 */
	protected function debug($message, array $context = [])
	{
		$this->logger->debug($message, $context);
	}

	/**
	 * @param string $message
	 * @param array $context
	 */
	protected function info($message, array $context = [])
	{
		$this->logger->info($message, $context);
	}

	/**
	 * @param string $message
	 * @param array $context
	 */
	protected function warn($message, array $context = [])
	{
		$this->logger->warn($message, $context);
	}

	/**
	 * @param string $message
	 * @param array $context
	 */
	protected function error($message, array $context = [])
	{
		$this->logger->warn($message, $context);
	}

	/**
	 * Check is job still running
	 *
	 * @return bool
	 */
	protected function isJobRunning()
	{
		//todo: check for job is still running
		return false;
	}

	/**
	 * @param string $key
	 * @param null $default
	 *
	 * @return mixed
	 */
	protected function config($key, $default = null)
	{
		$configPath = ( !empty($this->configPath) ? $this->configPath . '.' : '' ) . $key;
		return config($configPath, $default);
	}

  protected function getVendor()
  {
      return $this->vendor;
  }

  protected function getLogger()
  {
      return $this->logger;
  }
}
