<?php

namespace App\Jobs\Import\Volgaline;

use App\Model\CruiseSource;
use App\Model\Ship;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Class DeleteOldCruisesJob
 * @package App\Jobs\Import\Volgaline
 */
class DeleteOldCruisesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	/**
	 * @var array
	 */
    protected $cruiseVendorIds = [];


    protected $ship;

	/**
	 * DeleteOldCruisesJob constructor.
	 *
	 * @param array $cruiseVendorIds
	 */
    public function __construct(Ship $ship, array $cruiseVendorIds = [])
    {
        $this->cruiseVendorIds = $cruiseVendorIds;
        $this->ship = $ship;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
	    $this->ship->cruises()->whereNotIn('vendorId',$this->cruiseVendorIds)->delete();
	    //todo: logging ship deletion
    }
}
