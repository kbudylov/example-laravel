<?php

namespace App\Jobs\Import\Volgaline;

use App\Model\CruiseSource;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class DeleteOldShipsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	/**
	 * @var array
	 */
    protected $shipVendorIds = [];

	/**
	 * DeleteOldShipsJob constructor.
	 *
	 * @param array $shipVendorIds
	 */
    public function __construct(array $shipVendorIds = [])
    {
        $this->shipVendorIds = $shipVendorIds;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
	    CruiseSource::findByPrefix('volgaline')->ships()->whereNotIn('vendorId',$this->shipVendorIds)->delete();
	    //todo: logging ship deletion
    }
}
