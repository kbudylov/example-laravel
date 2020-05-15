<?php

namespace App\Model;

use App\Model\Booking\Order;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Client
 * @package App\Model
 * @property int $id
 * @property int $crmId
 * @property string $name
 * @property string $fullName
 * @property string $lastName
 * @property string $firstName
 * @property string $surName
 * @property string $email
 * @property string $phone
 * @property string $phoneDob
 * @property Collection $bookingOrders
 */
class Client extends Model
{
    use SoftDeletes;
    /**
     * @var string
     */
    protected $table = 'BookingClients';

    /**
     * @var bool
     */
    public $timestamps = true;

    /**
     * @var array
     */
    public $dates = ['created_at','updated_at','deleted_at'];

    /**
     * @var array
     */
    protected $fillable = ['name','email','phone','phoneDob','crmId','firstName','lastName','surName'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function bookingOrders()
    {
        return $this->hasMany(Order::class, 'clientId', 'id');
    }

    /**
     * @return string
     */
    public function getFullNameAttribute()
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getLastNameAttribute()
    {
        $names = preg_split("/ +/",$this->fullName, 3, PREG_SPLIT_NO_EMPTY);
        return !empty($names[0]) ? $names[0] : null;
    }

    /**
     * @return string|null
     */
    public function getFirstNameAttribute()
    {
        $names = preg_split("/ +/",$this->fullName, 3, PREG_SPLIT_NO_EMPTY);
        return !empty($names[1]) ? $names[1] : null;
    }

    /**
     * @return string|null
     */
    public function getSurNameAttribute()
    {
        $names = preg_split("/ +/",$this->fullName, 3, PREG_SPLIT_NO_EMPTY);
        return !empty($names[2]) ? $names[2] : null;
    }

    /**
     * @return int|null
     */
    public function getPhoneDobAttribute()
    {
        $matches = $this->getPhoneParts();
        if($matches){
            return !empty($matches[1]) ? $matches[1] : null;
        }
        return null;
    }

    /**
     * @return string|int|null
     */
    public function getPhoneAttribute()
    {
        $parts = $this->getPhoneParts();
        return !empty($parts[0]) ? $parts[0] : null;
    }

    /**
     * @param array|int|string $phone
     */
    public function setPhoneAttribute($phone)
    {
        if(is_array($phone)){
            $this->attributes['phone'] = $phone[0].'/'.$phone[1];
        } elseif(is_scalar($phone)) {
            $this->attributes['phone'] = $phone;
        }
    }

    /**
     * @param string $value
     */
    public function setPhoneDobAttribute($value)
    {
        if($this->phone){
            $matches = $this->getPhoneParts();
            if(empty($matches[1])){
                if($value){
                    $this->phone .="/".$value;
                }
            } else {
                $this->phone = $matches[0].'/'.$matches[1];
            }
        }
    }

    /**
     * @return array
     */
    protected function getPhoneParts()
    {
        $phoneParts = preg_split("/\//",$this->attributes['phone'],2, PREG_SPLIT_NO_EMPTY);
        return $phoneParts;
    }
}
