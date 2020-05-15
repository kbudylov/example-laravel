<?php

namespace App\Jobs\Import\Infoflot;

use App\Model\Ship;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Class ShipOldCabinsDeleteJob
 * @package App\Jobs\Import\Infoflot
 */
class ShipOldCabinsDeleteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var array
     */
    protected $cabinsForSave = [];

    protected $ship;

    /**
     * ShipOldCabinsDeleteJob constructor.
     * @param Ship $ship
     * @param array $cabinsForSave
     */
    public function __construct(Ship $ship, array $cabinsForSave = [])
    {
        $this->cabinsForSave = $cabinsForSave;
        $this->ship = $ship;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->ship->cabins()->whereNotIn('id',$this->cabinsForSave)->delete();
    }
}
