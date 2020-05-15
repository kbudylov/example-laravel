<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 04.12.17
 * Time: 17:36
 */

namespace App\Model\Booking;

use App\Exceptions\InvalidArgumentException;
use App\Model\CruiseCabin;
use Illuminate\Support\Collection;

/**
 * Trait OrderAddCabins
 * @package App\Model\Booking
 * @mixin Order
 */
trait OrderAddCabins
{
    /**
     * @var Collection
     */
    protected $orderCabins;

    /**
     * @var Collection
     */
    protected $orderPassengers;

    /**
     * @param $cabinId
     * @param array $passengers
     * @return bool
     * @throws InvalidArgumentException
     */
    public function addCabinById($cabinId, array $passengers)
    {
        if(!$this->orderCabins){
            $this->orderCabins = new Collection();
        }
        if(!$this->orderPassengers){
            $this->orderPassengers = new Collection();
        }
        if (!$this->hasCabin($cabinId)) {

            $cabin = $this->findCabinById($cabinId);
            if ($cabin) {
                $this->validateCabin($cabin, $passengers);
                $this->orderCabins->put($cabinId, $cabin);
                $this->orderPassengers->put($cabinId,[]);
                if(!empty($passengers)){
                    foreach ($passengers as $passenger) {
                        $this->addCabinPassenger($cabinId, $passenger);
                    }
                }
                $this->priceIsDirty = true;
                return true;
            } else {
                throw new InvalidArgumentException("Cruise cabin [$cabinId] not found");
            }
        } else {
            throw new InvalidArgumentException("Cruise cabin [$cabinId] already exists in the order");
        }
    }

    /**
     * @param $cabinId
     * @return bool
     */
    public function hasCabin($cabinId)
    {
        return $this->orderCabins->has($cabinId);
    }

    /**
     * @param CruiseCabin $cabin
     * @param array $passengers
     */
    protected function validateCabin(CruiseCabin $cabin, array $passengers)
    {
        if (!$cabin->isAvailable()){
            throw new \RuntimeException("Cruise cabin [$cabin->id] is not available for booking");
        }

        if ($cabin->countPlaces < count($passengers)) {
            throw new \RuntimeException("Cruise cabin [$cabin->id] has not enough available places for given passengers count");
        }

        if ($cabin->cruiseId != $this->cruiseId) {
            throw new \RuntimeException("Cruise cabin [$cabin->id] not found in the cruise [$this->cruiseId]");
        }
    }

    /**
     * @param $cabinId
     * @param \StdClass $passenger
     */
    public function addCabinPassenger($cabinId, $passenger)
    {
        $_passengers = $this->orderPassengers->get($cabinId,[]);
        $this->validatePassenger($cabinId, $passenger);
        $_passengers[] = $passenger;
        $this->orderPassengers->put($cabinId,$_passengers);
    }

    /**
     * @param $cabinId
     * @return mixed
     */
    public function getCabinPassengers($cabinId)
    {
        if ($this->orderPassengers->has($cabinId)) {
            return $this->orderPassengers->get($cabinId);
        } else {
            throw new \RuntimeException("Cabin [$cabinId] passengers list is empty");
        }
    }


    /**
     * @param $cabinId
     * @param $passenger
     * @throws \RuntimeException
     */
    protected function validatePassenger($cabinId, $passenger)
    {
        //todo
    }

    /**
     * @return Collection
     */
    public function getCabins()
    {
        return $this->orderCabins->all();
    }

    /**
     * @return Collection
     */
    public function getPassengers()
    {
        return $this->orderPassengers->all();
    }
}