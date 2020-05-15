<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 05.12.17
 * Time: 1:10
 */

namespace App\Model\Booking;

use App\Model\Cruise;
use Carbon\Carbon;

/**
 * Trait OrderAttributes
 * @package App\Model\Booking
 * @mixin Order
 */
trait OrderAttributes
{
    public $passengerName;

    public $passengerEmail;

    public $passengerPhone;

    /**
     * @return bool
     */
    public function getIsHashExpiredAttribute()
    {
        return Carbon::parse($this->hash_expires)->diffInMinutes(Carbon::now()) > BookingOrder::HASH_EXPIRES_MINUTES;
    }

    /**
     * @return string
     */
    public function getSummaryAttribute()
    {
        $cruise = Cruise::findById($this->cruiseId);
        $cabinNumbers = [];
        $passengersInfo = [];
        $passengersCount = 0;
        $passengersList = [];
        foreach ($this->cabins as $bookingCabin){
            /** @var Cabin $bookingCabin */
            $cabinNumbers[] = $bookingCabin->cabin->number;
            $passengerInfo = "Каюта №".$bookingCabin->cabin->number.": ";
            $passengersCategories = [];

            foreach ($bookingCabin->passengers as $bookingPassenger){
                $passengersList[$bookingCabin->cabin->number][] = $bookingPassenger;
            }

            foreach ($passengersList as $cabinNumber => $passengers){
                foreach ($passengers as $passenger){
                    $passengersCount++;
                    if(!isset($passengersCategories[$passenger->categoryId])){
                        $passengersCategories[$passenger->categoryId] = 0;
                    }
                    $passengersCategories[$passenger->categoryId]++;
                }
            }

            if(!empty($passengersCategories)){
                if(!empty($passengersCategories[1])){
                    $passengerInfo .= "; взрослых: ".$passengersCategories[1];
                }
                if(!empty($passengersCategories[2])){
                    $passengerInfo .= "; детских: ".$passengersCategories[2];
                }
                $passengersInfo[] = $passengerInfo;
            }
        }

        $passengersListTexts = [];
        foreach ($passengersList as $cabinNumber => $passengers){
            //todo: generate passengers list
            /**
             * 'categoryId' => $passengerInfo->categoryId,
            'gender' => $passengerInfo->gender,
            'firstName' => $passengerInfo->firstName,
            'lastName' => $passengerInfo->lastName,
            'surName' => $passengerInfo->middleName,
            'birthDate' => !empty($passengerInfo->birthDate) ? Carbon::parse($passengerInfo->birthDate)->toDateString() : null,
            'documentSeries' => !empty($passengerInfo->documentSeries) ? $passengerInfo->documentSeries : null,
            'documentNumber' => !empty($passengerInfo->documentNumber) ? $passengerInfo->documentNumber : null,
            'phoneNumbers' => json_encode(!empty($passengerInfo->phone) ? $passengerInfo->phone : [])
             */
        }

        $text = "Информация о заказе:
==============================================
Клиент (указано в форме):
Имя: ".$this->client->name.";
Почта: ".$this->client->email.";
Телефон: ".$this->client->phone.";
";
        //TODO: add passengers credentials
        if(!empty($this->comment)){
            $text.="
===============================================
Комментарий к заказу:

".$this->comment."
";
        }
        return $text;
    }
}