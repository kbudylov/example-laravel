<?php

namespace App\Jobs\Sync\Volgaline;

use App\Jobs\Sync\SyncJob;
use Facades\App\Components\Vendor\Volgaline\Client;
use App\Model\Cruise;
use App\Model\CruiseCabin;

/**
 * Class SyncCruiseCabinStatusesJob
 * @package App\Jobs\Sync\Volgaline
 */
class SyncCruiseCabinStatusesJob extends SyncJob
{
    /**
    * @inheritdoc
    */
    protected $configPath = 'import.vendors.volgaline';

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
		$cruise = $this->cruise;
        $cruiseCabins = Client::getCruiseCabinListByCruiseId($cruise->vendorId);

        $cabinCurrentIds = [];

        /** @var \StdClass $cruiseCabinInfo */
        foreach ($cruiseCabins as $k => $cruiseCabinInfo) {
            try {
                /** @var CruiseCabin $cruiseCabin */
                $cruiseCabin = CruiseCabin::where([
                    'cruiseId' => $this->getCruise()->id,
                    'vendorId' => $cruiseCabinInfo->id
                ])->first();
                if($cruiseCabin){

                    $cabinCurrentIds[] = $cruiseCabinInfo->id;

                    $cruiseCabin->saleStatusId = $cruiseCabinInfo->saleStatusId;
                    $cruiseCabin->isAvailable = $cruiseCabinInfo->isAvailable;
                    if($cruiseCabin->save()){
                        //$this->info('Cruise cabin #'.$cruiseCabin->id.' saved');
                    } else {
                        //$this->warn('Error occurs while saving cruise cabin #'.$cruiseCabin->id);
                    }
                } else {
                    //todo: we need to create cruise cabin here
                    //$this->warn('Cruise cabin [vendorId: '.$cruiseCabinInfo->id.' for cruise #'.$cruiseCabinInfo->cruiseId.'] not found');
                }
            } catch(\Exception $e) {
                //$this->error('Exception occurs while processing CruiseCabin [vendorId: '.$cruiseCabinInfo->id.']: '.$e->getMessage().'; in file '.$e->getFile().'; on line: '.$e->getLine());
            }
        }

        //deleting missing cruise cabins
        if (!empty($this->cabinIds)) {
            $cruise->cabins()->whereNotIn('vendorId',$cabinCurrentIds)->delete();
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
