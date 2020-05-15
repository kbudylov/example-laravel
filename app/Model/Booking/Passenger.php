<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 11.04.17
 * Time: 16:49
 */

namespace App\Model\Booking;

use App\Model\Cruise;
use App\Model\CruiseCabin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Passenger
 * @package App\Model\Booking
 * @property int $id
 * @property int $orderId
 * @property int $cruiseId
 * @property int $cabinId
 * @property int $placeId
 * @property int $categoryId
 * @property int $gender
 * @property string $firstName
 * @property string $lastName
 * @property string $middleName
 * @property string $birthDate
 * @property string $documentSeries
 * @property string $documentNumber
 * @property string $phoneNumbers
 * @property string $comment
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * relations *******************
 * @property Order $order
 * @property Cruise $cruise
 * @property CruiseCabin $cabin
 */
class Passenger extends Model
{
    use SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'BookingPassengers';

    /**
     * @var array
     */
    protected $fillable = [
        'orderId','cruiseId','cabinId','categoryId',
        'firstName','lastName','middleName','gender',
        'birthDate','documentNumber',
        'phoneNumbers'
    ];

    /**
     * @var array
     */
    protected $dates = [
        'created_at','updated_at','deleted_at'
    ];


    /**
     * Genders list
     */
    const GENDER_UNDEFINED = null;
    const GENDER_FEMALE = 1;
    const GENDER_MALE = 1;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'orderId', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cruise()
    {
        return $this->belongsTo(Cruise::class,'cruiseId','id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cabin()
    {
        return $this->belongsTo(CruiseCabin::class, 'cabinId','id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    //public function place()
    //{
    //    return $this->belongsTo(CruiseCabinPlace::class,'placeId','id');
    //}

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function category()
    {
        //TODO: get category id
        //return $this->hasOne(PriceCategory::class,'id','categoryId');
    }

    /**
     * @param $value
     */
    public function setDocumentSeriesAttribute($value)
    {
        $this->documentNumber = $value.$this->documentNumber;
    }

    /**
     * @return null
     */
    public function getDocumentSeriesAttribute()
    {
        return null;
    }

    /**
     * @return string
     */
    public function getFullNameAttribute()
    {
        return $this->lastName . ' ' . $this->firstName . (!empty($this->middleName) ? ' '.$this->middleName : '');
    }

    /**
     * @return array
     */
    public function getPhoneNumbersAttribute()
    {
        return $this->attributes['phoneNumbers'] ? json_decode($this->attributes['phoneNumbers']) : [];
    }

    /**
     * @param array $value
     */
    public function setPhoneNumbersAttribute($value)
    {
        if(is_array($value)){
            $this->attributes['phoneNumbers'] = json_encode($value);
        } elseif(is_string($value)) {
            $decoded = json_decode($value);
            if($decoded){
                $this->attributes['phoneNumbers'] = json_encode($decoded);
            } else {
                $this->attributes['phoneNumbers'] = json_encode([$value]);
            }
        }

    }
}