<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 18.04.17
 * Time: 14:25
 */

namespace App\Components\B24\Entity;

use App\Components\B24\Crm\Entity\Adapter;
use App\Model\CruiseDirection;
use App\Model\CruiseCabin;
use App\Model\Ship;
use App\Model\ShipDeck;
use App\Model\ShipCabin;
use Carbon\Carbon;

/**
 * Class DealAdapter
 * @package App\Components\B24\Entity
 */
class DealAdapter extends Adapter
{
    protected static $fieldMap = [
        'ID' => 'id',
        'TITLE' => 'title',
        'TYPE_ID' => 'typeId',
        'STAGE_ID' => 'stageId',
        'PROBABILITY' => 'probability',
        'CURRENCY_ID' => 'currencyId',
        'OPPORTUNITY' => 'opportunity',
        'TAX_VALUE' => 'taxValue',
        'LEAD_ID' => 'leadId',
        'COMPANY_ID' => 'companyId',
        'CONTACT_ID' => 'contactId',
        'QUOTE_ID' => 'quoteId',
        'BEGINDATE' => 'beginDate',
        'CLOSEDATE' => 'closeDate',

        'UF_CRM_1463858553' => 'operator',
        'UF_CRM_1463858715' => 'agencyRouteId',
        'UF_CRM_1463858736' => 'agencyShip',

        'UF_CRM_1463858927' => 'cruiseType',

        'UF_CRM_1463858089' => 'ourRoute',
        'UF_CRM_1463858605' => 'ourShip',

        'UF_CRM_1463858812' => 'departurePort',
        'UF_CRM_1463858831' => 'arrivalPort',

        'UF_CRM_1463858004' => 'departureDate',
        'UF_CRM_1463864357' => 'departureTime',

        'UF_CRM_1463864332' => 'arrivalDate',
        'UF_CRM_1463864370' => 'arrivalTime',

        'UF_CRM_1463858883' => 'bookingUntil',

        'UF_CRM_1463858640' => 'cabinNumber',
        'UF_CRM_1463866136' => 'deckId',
        'UF_CRM_1465851839' => 'bunkTypeId',
        'UF_CRM_1465852044' => 'cabinPlacesCount',
        'UF_CRM_1465852184' => 'cabinClassId',
        'UF_CRM_1465852627' => 'cabinAmenitiesId',
        'UF_CRM_1485956031' => 'separateId',
        'UF_CRM_1482581140' => 'passengerCount',

        'UF_CRM_1465853128' => 'cabinAdditionalNumbers',

        'UF_CRM_1467115819' => 'passenger1',
        'UF_CRM_1463858693' => 'passenger2',
        'UF_CRM_1465851258' => 'passenger3',
        'UF_CRM_1465851292' => 'passenger4',
        'UF_CRM_1477049706' => 'passenger5',
        'UF_CRM_1477049726' => 'passenger6',

        'UF_CRM_1467115832' => 'passport1',
        'UF_CRM_1465851322' => 'passport2',
        'UF_CRM_1465851334' => 'passport3',
        'UF_CRM_1465851350' => 'passport4',
        'UF_CRM_1477049742' => 'passport5',
        'UF_CRM_1477049754' => 'passport6',

        'UF_CRM_1467814854' => 'attachments',
        'UF_CRM_1486500602' => 'excursion',

        'UF_CRM_1522825922' => 'deckTitle',
        'UF_CRM_1511897905' => 'cabinDescription',
        'UF_CRM_1522940347' => 'cabinInfo'
    ];

    protected static $fieldDefaults = [
        'currencyId' => 'RUB',
        //'operator' => 114,
        //'cruiseType' => 140,
        'excursion' => 302
    ];

    public function setCruiseTypeAttribute($value)
    {
        //isOurCruise ? our : agency
        return $value ? 140 : 142;
    }

    /**
     * Туроператор
     * @param $value
     * @return int
     */
    public function setOperatorAttribute($value)
    {
        $opArray = [
            'volgaline' => 114,
            'infoflot' => 122,
            'vodohod' => 126
            /*
            114 => "volgaline",
            238 => 'Русь (Активный тур)',
            122 => "Инфофлот",
            116 => 'Цезарь Тревел',
            126 => 'Водоход (Речное агентство)',
            118 => 'Гама',
            120 => 'Белый Лебедь',
            124 => 'Ортодокс',
            128 => 'Спутник Гермес',
            130 => 'Латти',
            132 => 'Речфлот',
            282 => 'Магазин Путешествий'
            */
        ];

        return isset($opArray[$value]) ? $opArray[$value] : 114;
    }

    /**
     * @param Ship $ship
     * @return mixed
     */
    public function setOurShipAttribute($ship)
    {
        $ships = [
            134 => 'Александр Свирский',
            136 => 'Рихард Зорге',
            138 => ' '
        ];
        if(!$ship){
            return 138;
        }
        if($ship instanceof Ship){
            $ship = ['title' => $ship->title];
        }
        $shipId = array_search($ship['title'], $ships);
        return $shipId ? $shipId : 138;
    }

    /**
     * @param CruiseDirection $direction
     * @return mixed
     */
    public function setAgencyRouteIdAttribute($direction)
    {
        if($direction instanceof CruiseDirection){
            return $direction->title;
        } elseif (is_array($direction)) {
            return $direction['title'];
        }
    }

    /**
     * @param $value
     * @return string
     */
    public function setDepartureDateAttribute($value)
    {
        return Carbon::parse($value)->toDateString();
    }

    /**
     * @param $value
     * @return string
     */
    public function setArrivalDateAttribute($value)
    {
        return Carbon::parse($value)->toDateString();
    }

    /**
     * @param ShipDeck $deck
     * @return mixed|null
     */
    public function setDeckIdAttribute($deck)
    {
        return $deck['title'];
    }

    /**
     * @param $value
     * @return mixed|null
     */
    public function setCabinPlacesCountAttribute($value)
    {
        $countPlaces = [
            1 => 190,
            2 => 192,
            3 => 194,
            4 => 196
        ];
        return isset($countPlaces[$value]) ? $countPlaces[$value] : null;
        /**
         * "isRequired": true
         * 0 => {#929
        +"ID": "190"
        +"VALUE": "1-местная"
        }
        1 => {#930
        +"ID": "192"
        +"VALUE": "2-местная"
        }
        2 => {#931
        +"ID": "194"
        +"VALUE": "3-местная"
        }
        3 => {#932
        +"ID": "196"
        +"VALUE": "4-местная"
        }
         */
    }

    /**
     * @param $value
     * @return int
     */
    public function setBunkTypeIdAttribute($value)
    {
        /**
         * "isRequired": true
         * 0 => {#926
        +"ID": "186"
        +"VALUE": "Одноярусная"
        }
        1 => {#927
        +"ID": "188"
        +"VALUE": "Двухъярусная"
        }
         */
        return $value ? 188 : 186;
    }

    public function setCabinClassIdAttribute($value)
    {
        $classes = [
            'Стандарт' => 198,
            'Повышенной комфортности' => 200,
            'Полулюкс' => 202,
            'Люкс' => 204
        ];
        //первый
        //Стандарт
        //Повышенной комфортности
        //NULL
        if(isset($classes[$value])){
            return $classes[$value];
        } else {
            if($value == 'первый'){
                return 204;
            } else {
                return 198;
            }
        }
    }

    /**
     * @param CruiseCabin $value
     * @return int
     */
    public function setCabinAmenitiesIdAttribute($value)
    {
        $shipCabin = null;
        if(is_array($value)){
            $shipCabin = !empty($value['cabinId']) ? ShipCabin::find($value['cabinId']) : null;
        } elseif($value instanceof CruiseCabin) {
            $shipCabin = $value->cabin;
        }

        /**
         * "isRequired": true
         * 0 => {#939
        +"ID": "206"
        +"VALUE": "С удобствами"
        }
        1 => {#940
        +"ID": "208"
        +"VALUE": "С умывальником"
        }
        2 => {#941
        +"ID": "210"
        +"VALUE": "Без удобств"
        }
         */

        if($shipCabin){
            if($shipCabin->hasAmenity('туалет') || $shipCabin->hasAmenity('душ')){
                return 206;
            } elseif($shipCabin->hasAmenity('умывальник')) {
                return 208;
            }
        }
        return 210;
    }

    /**
     * @param $value
     *
     * @return bool
     */
    public function setSeparateIdAttribute($value)
    {
        return false;
    }
}