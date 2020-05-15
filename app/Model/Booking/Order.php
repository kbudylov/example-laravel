<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 03.12.17
 * Time: 4:55
 */

namespace App\Model\Booking;

use App\Components\Vendor\CalculatorInterface;
use App\Exceptions\InvalidArgumentException;
use App\Model\Cruise;
use App\Model\CruiseCabin;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Order
 * @package App\Model\Booking
 * @property int $id
 * @property int $cruiseId
 * @property int $clientId
 * @property int $dealId
 * @property float $totalPrice
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 * @property CalculatorInterface $calculator
 */
class Order extends Model
{
    use SoftDeletes, OrderAddCabins, OrderAddClient,
        OrderAttributes, OrderRelations;

    /**
     * Payment types
     */
    const PAY_TYPE_CASH = 'cash';
    const PAY_TYPE_ONLINE = 'online';
    const PAY_TYPE_RESERVE = 'reserve';

    /**
     *
     */
    const HASH_EXPIRES_MINUTES = 30;

    /**
     *
     */
    const BOOKING_EXPIRES_DAYS = 3;

    /**
     * Default passenger category ID
     */
    const DEFAULT_PASSENGER_CATEGORY = 1;

    /**
     * Order statuses
     */
    const STATUS_ACTIVE = 1;    //Just created
    const STATUS_PAYED = 2;     //Payed
    const STATUS_EXPIRED = 3;   //Expired
    const STATUS_CANCELLED = 4; //Cancelled

    /**
     * @var string
     */
    protected $table = 'BookingOrders';

    /**
     * @var array
     */
    protected $fillable = ['cruiseId','clientCrmId'];

    /**
     * @var array
     */
    protected $attributes = [
        'status' => self::STATUS_ACTIVE,
        'payType' => self::PAY_TYPE_RESERVE
    ];

    /**
     * @var Cruise
     */
    protected $orderCruise;

    /**
     * @var CalculatorInterface
     */
    protected $calculator;

    /**
     * @var array
     */
    protected $cabinPrices = [];

    /**
     * @var bool
     */
    protected $priceIsDirty = false;

    /**
     * Order constructor.
     * @param array $attributes
     * @throws InvalidArgumentException
     */
    public function __construct(array $attributes = [])
    {
        $this->hydrateAttributes($attributes);
        parent::__construct($attributes);
    }

    /**
     * @param CalculatorInterface $calculator
     */
    public function setPriceCalculator(CalculatorInterface $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * @return float
     */
    public function getTotalPriceAttribute()
    {
        if($this->priceIsDirty){
            $this->calculatePrice();
        }
        return $this->attributes['totalPrice'];
    }

    /**
     * @param $cabinId
     * @return mixed
     */
    public function getCabinPrice($cabinId)
    {
        if($this->priceIsDirty){
            $this->calculatePrice();
        }
        foreach ($this->cabinPrices as $cabinInfo){
            if($cabinInfo['cruiseCabinId'] == $cabinId){
                $priceTotal = 0;
                foreach ($cabinInfo['Prices'] as $passengerCategoryPrices) {
                    $priceTotal += $passengerCategoryPrices['priceTotal'];
                }
                return $priceTotal;
            }
        }
        throw new \RuntimeException("Cabin [$cabinId] prices list is empty");
    }

    public function getCabinPrices()
    {
        return $this->cabinPrices;
    }

    /**
     *
     */
    public function calculatePrice()
    {
        $result = $this->calculator->calculate($this);
        if ($result) {
            $this->totalPrice = $result->getTotalPrice();
            $this->cabinPrices = $result->getCabinsPrices();
            $this->priceIsDirty = false;
        } else {
            throw new \RuntimeException('Unable to calculate result');
        }
    }

    /**
     * @param $cabinId
     * @return CruiseCabin
     */
    protected function findCabinById($cabinId)
    {
        $cabin = CruiseCabin::findById($cabinId);
        if ($cabin) {
            return $cabin;
        } else {
            throw new \RuntimeException("Cabin [$cabinId] not found");
        }
    }

    /**
     * @param array $attributes
     * @throws InvalidArgumentException
     */
    protected function hydrateAttributes(array $attributes=[])
    {
        if (isset($attributes['cruiseId'])) {
            $this->orderCruise = Cruise::findById($attributes['cruiseId']);
            if ($this->orderCruise) {
                if(isset($attributes['cruiseCabins'])){
                    foreach ($attributes['cruiseCabins'] as $cabinId => $passengers) {
                        $this->addCabinById($this->orderCruise->cabin($cabinId)->id, $passengers);
                    }
                    unset($attributes['cruiseCabins']);
                }
            } else {
                throw new InvalidArgumentException("Cruise ${attributes['cruiseId']} not found");
            }
        }

        if (isset($attributes['client'])) {
            $this->setClientCredentials($attributes['client']);
            unset($attributes['client']);
        }
    }

    public function beforeSave()
    {
        $cruise = Cruise::findById($this->cruiseId);
        if ($cruise) {
            return $cruise->vendorRel->getFactory($cruise)->beforeCreateBookingOrder($this);
        }
    }

    public function afterSave()
    {
        $cruise = Cruise::findById($this->cruiseId);
        if ($cruise) {
            return $cruise->vendorRel->getFactory($cruise)->afterCreateBookingOrder($this);
        }
    }

    public function beforeUpdate()
    {
        $cruise = Cruise::findById($this->cruiseId);
        if ($cruise) {
            return $cruise->vendorRel->getFactory($cruise)->beforeUpdateBookingOrder($this);
        }
    }

    public function afterUpdate()
    {
        $cruise = Cruise::findById($this->cruiseId);
        if ($cruise) {
            return $cruise->vendorRel->getFactory($cruise)->afterUpdateBookingOrder($this);
        }
    }

    public function beforeDelete()
    {
        $cruise = Cruise::findById($this->cruiseId);
        if ($cruise) {
            return $cruise->vendorRel->getFactory($cruise)->beforeDeleteBookingOrder($this);
        }
    }

    public function afterDelete()
    {
        $cruise = Cruise::findById($this->cruiseId);
        if ($cruise) {
            return $cruise->vendorRel->getFactory($cruise)->afterDeleteBookingOrder($this);
        }
    }

    /**
     * @param $dealId
     * @return static
     */
    public static function findByDealId($dealId)
    {
        return static::withTrashed()->where([
            'dealId' => $dealId
        ])->first();
    }

    /**
     *
     */
    public function setExpired()
    {
        $this->status = static::STATUS_EXPIRED;
        $this->save();
    }

    /**
     * @throws \Exception
     */
    public function setCancelled()
    {
        $this->status = static::STATUS_CANCELLED;
        $this->save();
        $this->delete();
    }

    /**
     *
     */
    public function setPayed()
    {
        $this->status = static::STATUS_PAYED;
        $this->save();
    }

    /**
     * @return bool
     */
    public function isCancelled()
    {
        return $this->status == static::STATUS_CANCELLED;
    }

    /**
     * @return bool
     */
    public function isPayed()
    {
        return $this->status == static::STATUS_PAYED;
    }
}