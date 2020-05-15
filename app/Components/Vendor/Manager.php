<?php
/**
 * Created by Konstantin Budylov.
 * Mailto: k.budylov@gmail.com
 * Date: 01.11.17 20:41
 **********************************************************************************/

namespace App\Components\Vendor;

use App\Components\Vendor\Infoflot\Facade as InfoflotFacade;
use App\Components\Vendor\Volgaline\Facade as VolgalineFacade;
use App\Jobs\Import\ImportJob;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;

/**
 * Class Manager
 * @package App\Components\Vendor
 */
class Manager extends ServiceProvider
{
	/**
	 * @var array
	 */
	protected $vendors = [];

	/**
	 * ClientManager constructor.
	 *
	 * @param \Illuminate\Contracts\Foundation\Application $application
	 */
	public function __construct($application)
	{
		parent::__construct($application);

		foreach (config('import.vendors',[]) as $vendorName => $vendorConfig) {
			if ($vendorConfig['enabled']) {
				$this->vendors[$vendorName] = $vendorConfig;

				//todo: configuring parser service providers
			}
		}
	}

	/**
	 *
	 */
	public function boot()
	{

	}

	/**
	 *
	 */
	public function register()
	{
		$this->app->singleton('volgaline', VolgalineFacade::class );
		$this->app->singleton('Infoflot', InfoflotFacade::class );
	}

	/**
	 * @throws \InvalidArgumentException
	 */
	public function import()
	{
		foreach ($this->vendors as $vendorName => $vendorConfig) {
			if(Arr::has($vendorConfig,'import.jobs.general')){
				$this->dispatchImportJob(Arr::get($vendorConfig, 'import.jobs.general',[]));
			} else {
				throw new \InvalidArgumentException('Configuration for job [general] is undefined');
			}
		}
	}

    /**
     * @param $vendor
     *
     * @return bool
     * @throws \Exception
     */
	public function importVendor($vendor)
	{
		if(isset($this->vendors[$vendor])){
			$vendorConfig = $this->vendors[$vendor];
			if(Arr::has($vendorConfig,'import.jobs.general')){
				$this->dispatchImportJob(Arr::get($vendorConfig, 'import.jobs.general',[]));
			} else {
				throw new \InvalidArgumentException('Configuration for job [general] is undefined');
			}
		} else {
			throw new \Exception("Vendor [$vendor] is not exists or disabled by configuration");
		}
		return true;
	}

	/**
	 * @param array $jobConfig
	 *
	 * @return ImportJob
	 */
	protected function dispatchImportJob(array $jobConfig)
	{
		if (!empty($jobConfig['class'])) {
			$jobClassname = $jobConfig['class'];
		} else {
			throw new \InvalidArgumentException('Job class name is undefined');
		}
		$jobQueue = Arr::has($jobConfig,'queue') ? Arr::get($jobConfig,'queue') : 'default';
		return  ($jobClassname)::dispatch()->onQueue($jobQueue);
	}
}
