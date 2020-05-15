<?php

namespace App\Jobs\Sync\Infoflot;

use App\Jobs\Sync\SyncJob;
use App\Model\Cruise;
use Facades\App\Components\Vendor\Infoflot\Client;

/**
*
**/
class SyncCruiseCabinStatusesJob extends SyncJob
{
    /**
    * @inheritdoc
    */
    protected $configPath = 'import.vendors.infoflot';

    protected $cruise;

    public function __construct(Cruise $cruise)
    {
        $this->cruise = $cruise;
        parent::__construct($cruise);
    }
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->setStatuses($this->cruise);
    }

    public function setStatuses(Cruise $cruise)
    {
	    $cabinsStatusesList = Client::getCruiseCabinsStatusList($cruise->ship->vendorId, $cruise->vendorId);

      	if(!empty($cabinsStatusesList)) {
          	$cabinsAvailable = [];
          	foreach ($cabinsStatusesList as $cabinId => $cabinInfo) {
                if ($cabinInfo->status == 0) {
                    $cabinsAvailable[] = $cabinId;
                }
          	}
      	} else {
		    $this->info('Cruise [] cabin statuses list is empty');
	    }

        //Set all cabins as unavailable
        \DB::update("
            UPDATE `CruiseCabin`
            SET `saleStatusId` = 2, `isAvailable` = 0
            WHERE `cruiseId` = ?",[$cruise->id]);

      	//Set available cabins
        if(!empty($cabinsAvailable)){
            $_vendorIds = implode(", ", $cabinsAvailable);
            \DB::update("
                UPDATE `CruiseCabin`
                SET `saleStatusId` = 1, `isAvailable` = 1
                WHERE `cruiseId` = ? AND cabinId IN (
                SELECT `id` FROM `ShipCabin` WHERE `shipId` = ? AND vendorId IN (".$_vendorIds.")
            )",[
                $cruise->id,
                $cruise->shipId,
            ]);
        }

        $cruise->countAvailable = $this->getCountAvailableCabins($cruise->id);
        $cruise->save();
    }

    /**
     * @param $cruiseId
     *
     * @return int
     */
    protected function getCountAvailableCabins($cruiseId)
    {
        $result = \DB::selectOne(' SELECT COUNT(*) as count
                                    FROM CruiseCabin C
                                    WHERE C.cruiseId = ? AND isAvailable = 1',[$cruiseId]);

        if(!empty($result->count)){
            return $result->count;
        }
        return 0;
    }
}
