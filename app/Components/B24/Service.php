<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 01.05.17
 * Time: 23:00
 */

namespace App\Components\B24;

use App\Components\B24\Entity\Contact;
use App\Components\B24\Entity\Deal;
use App\Components\Validator\PhoneInputValidator;
use App\Model\Booking\Cabin as BookingCabins;
use App\Model\Booking\Order as BookingOrder;
use App\Model\Booking\Passenger as BookingPassenger;
use App\Model\Client;
use App\Model\Cruise;
use App\Model\CruiseCabin;
use Carbon\Carbon;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * TODO: make this as a service provider
 * Class Service
 * @package App\Components\B24
 */
class Service
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * Service constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->logger = new Logger('crm-service-logger');
        $this->logger->pushHandler(new StreamHandler(__DIR__.'/../../../storage/logs/b24.service.log', Logger::DEBUG));
    }

    /**
     * @param BookingOrder $order
     * @return Crm\Entity\Entity|null|static
     */
    public function crmCreateDeal(BookingOrder $order)
    {
        $attributes = $this->prepareAttributes($order);
        $deal = Deal::create($attributes);
        if($deal){
            $this->logger->info('Deal created success',['id' => $deal->id]);
            $this->crmDealAddProducts($deal, $order);
            return $deal;
        } else {
            $this->logger->error('Deal create error');
        }
        return null;
    }

    /**
     * @param BookingOrder $order
     * @return bool
     */
    public function crmUpdateDeal(BookingOrder $order)
    {
        if($order->dealId){
            $attributes = $this->prepareAttributes($order);
            $deal = Deal::get($order->dealId);
            if ($deal) {
                $deal->setAttributes($attributes);
                if($deal->save()){
                    $this->crmDealAddProducts($deal, $order);
                }
            } else {
                throw new \RuntimeException('Deal ['.$order->dealId.'] not found in CRM, but id is exits in order ['.$order->id.']');
            }
        } else {
            throw new \RuntimeException('Order dealId is undefined');
        }
        return true;
    }

    /**
     * @param BookingOrder $order
     * @return array
     */
    protected function prepareAttributes(BookingOrder $order)
    {
        /** @var Cruise $cruise */
        $cruise = Cruise::findById($order->cruiseId);
        $cabins = $order->cabins;

        $firstCabin = [];
        $addCabins = [];
        $addCabinsNumbers = [];
        $passengersCount = 0;
        $passengersCategories = [];

        /** @var BookingCabins $bookingCabin */
        foreach ($cabins as $bookingCabin){
            if(empty($firstCabin)){
                $firstCabin = [
                    'number' => $bookingCabin->cabin->number,
                    'cabin' => $bookingCabin->cabin,
                    'isSeparate' => $bookingCabin->cabin->isSeparate
                ];
            } else {
                $addCabins[] = [
                    'number' => $bookingCabin->cabin->number,
                    'cabin' => $bookingCabin->cabin,
                    'isSeparate' => $bookingCabin->cabin->isSeparate
                ];
                $addCabinsNumbers[] = $bookingCabin->cabin->number;
            }

            /** @var BookingPassenger $bookingPlace */
            foreach ($bookingCabin->passengers as $passenger) {
                $passengersCount++;
                if(!isset($passengersCategories[$passenger->categoryId])){
                    $passengersCategories[$passenger->categoryId] = 0;
                }
                $passengersCategories[$passenger->categoryId]++;
            }
        }

        $vendorPrefix = $cruise->vendorRel->prefix;
        $isOurCruise = $vendorPrefix == 'volgaline';

        $contact = $this->contactFindOrNew($order->client);

        $attributes = [
            'contactId' => $contact ? $contact->id : null,
            'cruiseType' => $isOurCruise,
            'title' => 'Заказ #'.$order->id.': бронирование на сайте',
            'opportunity' => $order->totalPrice,
            'ourRoute' => $cruise->direction,
            'agencyRouteId' => $cruise->direction,
            'ourShip' => $isOurCruise ? $cruise->ship : null,
            'agencyShip' => $isOurCruise ? null : $cruise->ship->title,
            'beginDate' => Carbon::parse($order->created_at)->toDateString(),
            'bookingUntil' => Carbon::parse($order->expires_at)->toDateTimeString(),
            'comments' => nl2br($order->summary),
            'cabinNumber' => $firstCabin['cabin']->number,
            'departureDate' => $cruise->departureDate,
            'arrivalDate' => $cruise->returnDate,
            'departureTime' => $cruise->departureTime,
            'arrivalTime' => $cruise->returnTime,
            'deckId' => $firstCabin['cabin']->shipCabin->deck,
            'cabinPlacesCount' => $firstCabin['cabin']->countPlaces,
            'bunkTypeId' => $firstCabin['cabin']->shipCabin->isBunkCabin,
            'cabinClassId' => $firstCabin['cabin']->shipCabin->category->title,
            'cabinAmenitiesId' => $firstCabin['cabin'],
            'separateId' => $firstCabin['isSeparate'],
            'passengerCount' => $passengersCount,
            'cabinAdditionalNumbers' => implode(",",$addCabinsNumbers),
            'departurePort' => $cruise->departurePort,
            'arrivalPort' => $cruise->returnPort,
            //'roistat' => $order->roistat,
            'operator' => $vendorPrefix,
            'deckTitle' => $firstCabin['cabin']->shipCabin->deck->title,
            'cabinDescription' => $this->getCabinDescription($firstCabin['cabin']),
            'cabinInfo' => $this->getCabinInfo($firstCabin['cabin'])
        ];

        $passengersAttributes = $this->getPassengersCredentialsAttributes($order);
        foreach ($passengersAttributes as $key => $value){
            $attributes[$key] = $value;
        }

        return $attributes;
    }

    /**
     * @param CruiseCabin $cabin
     *
     * @return string
     */
    protected function getCabinDescription(CruiseCabin $cabin)
    {
        return $cabin->shipCabin->category->title . "\n\n" . $cabin->shipCabin->category->description;
    }

    /**
     * @param CruiseCabin $cabin
     *
     * @return string
     */
    protected function getCabinInfo(CruiseCabin $cabin)
    {
        $info = "";
        $info .= "Каюта №".$cabin->shipCabin->number."\n\r";
        $info .= "Категория: ".$cabin->shipCabin->category->title.".\n\r";
        if ($cabin->shipCabin->category->description) {
            $info .= $cabin->shipCabin->category->description."\n\r";
        }
        $info .= "Палуба: ".$cabin->shipCabin->deck->title."\n\r";
        return $info;
    }



    /**
     * @param Deal $deal
     * @param BookingOrder $order
     * @return bool|mixed
     */
    protected function crmDealAddProducts(Deal $deal, BookingOrder $order)
    {
        $productRows = [];

        //for long list of the products - use short variant of the product names
        $generateShortNames = false;
        $countCabins = $order->cabins->count();
        if($countCabins > 5){
            $generateShortNames = true;
        }


        foreach ($order->cabins as $bookingCabin) {
            /** @var  BookingCabins $bookingCabin */
            $productRow = [
                'productName' => $this->generateCrmProductName($order, $bookingCabin, $generateShortNames),
                'taxIncluded' => 'N',
                'measureCode' => 1,
                'measureName' => 'чел.',
                'price' => $bookingCabin->price,
            ];
            $productRows[] = $productRow;
        }
        return $deal->addProducts($productRows);
    }

    /**
     * @param BookingOrder $order
     * @param BookingCabins $bookingCabin
     * @param bool $generateShortNames
     * @return string
     */
    protected function generateCrmProductName(BookingOrder $order, BookingCabins $bookingCabin, $generateShortNames = false)
    {
        /** @var Cruise $cruise */
        $cruise = $order->cruise;

        if($generateShortNames){
            $productName = '№: %d (%s-мест.; %s-ярус.);';
            $productName = sprintf($productName,
                $bookingCabin->cabin->number,
                $bookingCabin->cabin->countPlaces,
                $bookingCabin->cabin->isBunkCabin ? '2' : '1'
            );

        } else {
            //Речной круиз (маршрут) (дата отправления-дата прибытия), (теплоход), (номер каюты), (взрослый/ребенок/пенсионер)
            $productName = 'Речной круиз: %s (%s-%s; т/х: "%s"); Каюта №: %d (%s-местная, %s-ярусная); ';
            $productName = sprintf($productName,
                $cruise->title, Carbon::parse($cruise->departureDate)->format('d.m.Y'), Carbon::parse($cruise->returnDate)->format('d.m.Y'),
                $cruise->ship->title,
                $bookingCabin->cabin->number,
                $bookingCabin->cabin->countPlaces,
                $bookingCabin->cabin->isBunkCabin ? '2' : '1'
            );

        }

        $passengerCategories = [];

        foreach ($bookingCabin->passengers as $bookingPassenger){
            if(!isset($passengerCategories[$bookingPassenger->categoryId])){
                $passengerCategories[$bookingPassenger->categoryId] = 0;
            }
            $passengerCategories[$bookingPassenger->categoryId]++;
        }

        $passengersSummary = [];
        if ($generateShortNames) {
            if(isset($passengerCategories[1])){
                $passengersSummary[] = $passengerCategories[1].' взр.';
            }
            if(isset($passengerCategories[2])){
                $passengersSummary[] = $passengerCategories[2].' д./п.';
            }
        } else {
            if(isset($passengerCategories[1])){
                $passengersSummary[] = 'взрослых: '.$passengerCategories[1];
            }
            if(isset($passengerCategories[2])){
                $passengersSummary[] = 'детей/пенсионеров: '.$passengerCategories[2];
            }
        }
        $productName .= implode(" / ",$passengersSummary);
        return $productName;
    }

    /**
     * @param $id
     * @return Contact|null
     */
    public function findContactById($id)
    {
        $contact = $this->findContactByAttributes([
            'ID' => $id
        ]);
        if($contact){
            return $contact;
        }
        return null;
    }

    /**
     * @param BookingOrder $order
     * @return Contact
     */
    public function contactFindOrNew(Client $client)
    {
        /*
        if ($client->crmId) {
            $contact = $this->findContactById($client->crmId);
            if(!$contact){
                throw new \RuntimeException('CRM contact ['.$client->crmId.'] not found.');
            }
        } else {
        */
            $phone = PhoneInputValidator::cleanup($client->phone);
            $phone = str_replace('+7','8',$phone);
            $contact = $this->findContactByAttributes([
                'PHONE' => $phone,
                'EMAIL' => $client->email
            ]);
            if(!$contact){
                $names = $this->splitFullName($client->name);
                $contact = Contact::create([
                    'name' => $names->firstName,
                    'lastName' => $names->lastName,
                    'secondName' => $names->surName,
                    'email' => $client->email,
                    'phone' => $phone,
                    'comments' => 'Данные указаны при бронировании с сайта:<br>Имя:'.$client->name."<br>Email:".$client->email."<br>Phone:".$phone
                ]);
                if(!$contact){
                    throw new \RuntimeException('Error occurs while creating contact');
                }
            }
            //TODO: burn it with fire!
            $client->crmId = $contact->id;
            $client->save();
        /*
        }
        */
        return $contact;
    }

    /**
     * @param BookingOrder $order
     * @return int
     */
    protected function getCompanyId(BookingOrder $order)
    {
        return null;
    }

    /**
     * @param array $attributes
     * @return null|Contact
     */
    public function findContactByAttributes(array $attributes)
    {
        $contactList = Contact::getList([
            'filter' => $attributes,
            'select' => [ "ID", "NAME", "LAST_NAME", "TYPE_ID", "SOURCE_ID","PHONE","EMAIL"]
        ])->toArray();

        if(!empty($contactList)){
            return $contactList[0];
        } else {
            return null;
        }
    }

    /**
     * @param $fullName
     * @return \StdClass
     */
    protected function splitFullName($fullName)
    {
        $names = preg_split('/\s+/i',$fullName,-1,PREG_SPLIT_NO_EMPTY);
        $result = new \StdClass();
        $cnt = count($names);
        if(count($names) === 2){
            $result -> lastName = $names[0];
            $result -> firstName = $names[1];
            $result -> surName = "";
        } elseif(count($names) === 3) {
            $result -> lastName = $names[0];
            $result -> firstName = $names[1];
            $result -> surName = $names[2];
        } elseif(count($names) === 1) {
            $result -> firstName = $names[0];
            $result -> lastName = "";
            $result -> surName = "";
        } elseif($cnt > 3) {
            $result -> firstName = $names[0];
            $result -> lastName = $names[1];
            $result -> surName = $names[2] . "(Full name: ".$fullName.")";
        } else {
            throw new \RuntimeException('Invalid name format: ['.$fullName.']');
        }
        return $result;
    }

    /**
     * @param BookingOrder $order
     * @return array
     */
    protected function getPassengersCredentialsAttributes(BookingOrder $order)
    {
        $attributes = [];
        /**
         * @var  BookingPassenger $bookingPassenger
         */
        foreach ($order->passengers as $k => $bookingPassenger) {
            if($k >= 6) {
                break; //only 6 passengers allowed in CRM at this time
            }
            $passengerAttributeName = 'passenger'.($k+1);
            $passportAttributeName = 'passport'.($k+1);

            $attributes[$passengerAttributeName] = $bookingPassenger->fullName;
            $attributes[$passportAttributeName] = $bookingPassenger->documentNumber;
        }
        return $attributes;
    }
}