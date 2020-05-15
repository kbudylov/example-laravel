<?php

namespace App\Model;

use App\Exceptions\BadRequestException;
use App\Exceptions\InvalidArgumentException;
use App\Model\Booking\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Cruise
 * @package App\Model
 * @property CruiseSource $vendorRel
 * @property array:CruiseCabin[] $cabins
 * @property array:CruiseCabinCategory[] $cabins
 */
class Cruise extends Model
{
    use CruiseFinder,
        CruiseAttributes,
        CruiseRelations,
        SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'Cruise';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var array
     */
    protected $fillable = [
        'title','shipId','vendorId','vendor','departurePointId','returnPointId',
        'departureDateTime','returnDateTime',
        'riverId','isWeekend','specialOffer',
        'description','priceInclude','priceNotInclude',
        'directionId','regionName','bookingPriceUrl','bookingUrl'
    ];

    /** @var  \StdClass */
    protected $_infoDecoded;


    /**
     * @param $id
     * @return CruiseCabin
     */
    public function cabin($id)
    {
        return $this->cabins()->where(['id' => $id])->firstOrFail();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cabinCategories()
    {
        return $this->hasMany(CruiseCabinCategory::class, 'cruiseId', 'id');
    }

    /**
     * @param array $attributes
     * @return Order
     * @throws \App\Exceptions\InvalidConfigException
     */
    public function createBookingOrder(array $attributes = [])
    {
        return $this->vendorRel->getFactory($this)->createBookingOrder($attributes);
    }

    /**
     * @param $params
     * @return Order
     * @throws BadRequestException
     * @throws InvalidArgumentException
     */
    /*
    public function createBookingPriceOrder($params)
    {
        $order = new Order([
            'cruiseId' => $this->id
        ]);

        if(!empty($params)){
            //Adding cabins to order
            foreach ($params as $k => $cabinOptions) {
                if (isset($cabinOptions->cruiseCabin)) {
                    $categoryList = isset($cabinOptions->priceCategory) ? $cabinOptions->priceCategory : [];
                    $passengers = [];
                    foreach ($categoryList as $category) {
                        $passengers[] = [
                            'category' => $category->id,
                            'count' => $category->count
                        ];
                    }
                    $cabin = $this->findCabin($cabinOptions->cruiseCabin);
                    $order->addCabinById($cabin->id, $passengers);
                } else {
                    throw new InvalidArgumentException("Undefined [cruiseCabin] in the parameter list item [$k]");
                }
            }

            return $order;

        } else {
            throw new BadRequestException('Booking params are undefined', 400);
        }
    }
    */

    /**
     * @param int $cabinId
     * @return CruiseCabin
     * @throws InvalidArgumentException
     */
    /*
    protected function findCabin($cabinId)
    {
        $cabin = $this->cabins()->where([
            'vendorId' => $cabinId
        ])->first();
        if (!$cabin) {
            $cabin = $this->cabins()->where(['id' => $cabinId])->first();
            if(!$cabin){
                throw new NotFoundHttpException("Cabin [".$cabinId."] not found");
            }
        }
        return $cabin;
    }
    */
}
