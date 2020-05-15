<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 22.06.17
 * Time: 20:45
 */

namespace App\Model;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Trait CruiseFinder
 * @package App\Model
 * @mixin Cruise
 */
trait CruiseFinder
{
    use CruiseScopes;

        /**
     * @param $id
     * @return Cruise
     */
    public static function findById($id)
    {
        return static::find($id);
    }

    /**
     * @return Collection
     */
    public static function findAll($orderColumn = 'id', $asc = true)
    {
        return static::active(config('worker.shipIdActive',[]))->orderByColumn($orderColumn,$asc)->get();
    }

    /**
     * @param $vendorId
     * @return Collection
     */
    public static function findAllByVendorId($vendorId)
    {
        return static::whereRaw('vendor = (SELECT id FROM `CruiseSource` WHERE `prefix` = ?)', [
            $vendorId
        ])->get();
    }

    /**
     * @param $shipId
     * @return mixed
     */
    public static function findAllByShipId($shipId)
    {
        return static::where(['shipId' => $shipId])->get();
    }

    /**
     * @param $directionId
     * @return mixed
     */
    public static function findAllBuDirectionId($directionId)
    {
        return static::where(['directionId' => $directionId])->get();
    }

    /**
     * @param ParameterBag $searchParams
     * @return Collection
     */
    public static function search(ParameterBag $searchParams, $order = null, $asc = true)
    {
        /** @var Builder $query */
        $query = static::active(config('worker.shipIdActive',[]))->searchByParams($searchParams)->orderByColumn($order, $asc);
        return $query->get();
    }

    /**
     * @param ParameterBag $searchParams
     * @return Collection
     */
    public static function searchFields(ParameterBag $searchParams, $order = null, $asc = true)
    {
        $returnData = [
            'months' => [],
            'duration' => [],
            'departure' => [],
            'ships' => [],
            'cities' => []
        ];

        /** @var Builder $query */
        $query = static::active(config('worker.shipIdActive',[]))->searchByParams($searchParams)->orderByColumn($order, $asc);

        $cruiseList = $query->get();

        $cruiseIds = [];
        $shipsIds = [];
        $departurePointIds = [];

        foreach ($cruiseList as $cruise) {
            $cruiseIds[] = $cruise->id;
            if(!in_array($cruise->shipId, $shipsIds)){
                $shipsIds[] = $cruise->shipId;
            }
            if(!in_array($cruise->departurePointId, $departurePointIds)){
                $departurePointIds[] = $cruise->departurePointId;
            }
        }

        if(!empty($cruiseIds)) {

            $_cities = \DB::select(
                "SELECT DISTINCT
                        GC.id,
                        GC.title
                    FROM `CruiseRoute` CR
                    JOIN CruiseRoutePoint CRP ON CRP.id = CR.pointId
                    JOIN GEOCity GC ON GC.id = CRP.cityId
                    WHERE CR.cruiseId IN (".implode(',',$cruiseIds).") ORDER BY title ASC");

            $returnData['cities'] = static::filterCities($_cities);

            $_months = \DB::select('SELECT YEAR(departureDateTime) AS `year`, MONTH(departureDateTime) as `month` FROM Cruise WHERE id IN ('.implode(', ',$cruiseIds).') ORDER BY month ASC');
            foreach ($_months as $monthData) {
                $yearNum = $monthData->year;
                $monthNum = $monthData->month > 9 ? (string)$monthData->month : '0'.$monthData->month;
                if(!isset($returnData['months'][$yearNum])){
                    $returnData['months'][$yearNum] = [];
                }
                if(!in_array($monthNum, $returnData['months'][$yearNum])) {
                    $returnData['months'][$yearNum][] = $monthNum;
                }
            }

            $_duration = \DB::select('SELECT (DATEDIFF(`returnDateTime`,`departureDateTime`) + 1) AS `countDays` FROM Cruise WHERE id IN ('.implode(', ',$cruiseIds).') ORDER BY countDays ASC');
            foreach ($_duration as $durationData) {
                if(!in_array($durationData->countDays,$returnData['duration'])){
                    $returnData['duration'][] = $durationData->countDays;
                }
            }

            $_sql = 'SELECT id, title, (showPriority > 0) AS selected FROM Ship WHERE id IN ('.implode(',',$shipsIds).') ORDER BY showPriority DESC, title ASC';
            $_ships = \DB::select($_sql);
            foreach ($_ships as $shipData) {
                $returnData['ships'][] = [
                    'id' => $shipData->id,
                    'title' => $shipData->title,
                    'selected' => $shipData->selected
                ];
            }

            $_departure = \DB::select('
                SELECT DISTINCT id, title FROM GEOCity
                WHERE id IN (
                    SELECT DISTINCT cityId FROM CruiseRoutePoint WHERE id IN ('.implode(',',$departurePointIds).')
                ) ORDER BY title ASC
            ');

            //todo: set unique index on cities table + cleanup infoflot response
            $returnData['departure'] = static::filterCities($_departure);
        }

        return $returnData;
    }

    /**
     * @return array
     */
    protected static function filterCities($citiesList)
    {
        $_citiesUnique = [];
        $_citiesFiltered = [];

        //todo: set unique index on cities table + cleanup infoflot response
        foreach ($citiesList as $city) {
            //filtering cities names
            //todo: при импорте убрать весь мусор из выдачи городов
            $title = trim($city->title);
            if(preg_match('/[^абвгдеёжзийклмнопрстуфхцчшщъыьэюяАБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯЙЦУКЕНГШЩЗХЪФЫВАПРОЛДЖЭЯЧСМИТЬБЮ -]/i',$title)
                || preg_match("/День/",$title)
                || preg_match("/Елабуга-Казань/",$title)
            ){
                continue;
            } elseif( preg_match("/ьь/", $title) ) {
                $title = preg_replace("/ьь/","ь",$title);
            } else if($title == "Улич") {
                continue;
            } else if(preg_match("/ - /",$title)) {
                continue;
            }
            if(!isset($_citiesUnique[$title])){
                $_citiesUnique[$title] = $city;
                $_citiesFiltered[] = [
                    'id' => $city->id,
                    'title' => $title
                ];
            } else {

            }
        }

        return $_citiesFiltered;
    }
}