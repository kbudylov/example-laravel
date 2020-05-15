<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 04.12.17
 * Time: 16:50
 */

namespace App\Model;

use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Trait CruiseScopes
 * @package App\Model
 * @mixin Cruise
 */
trait CruiseScopes
{
    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query, $shipIds = [])
    {
        return $query->notStartedYet()->onlyWithCabins()->onlyRussian()->vendor('volgaline', $shipIds);
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeNotStartedYet(Builder $query)
    {
        return $query->whereRaw('departureDateTime >= CURDATE()');
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeOnlyRussian(Builder $query)
    {
        return $query->whereRaw('regionName = ?',['Россия']);
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeOnlyWithCabins(Builder $query)
    {
        return $query->whereRaw('Cruise.id IN (SELECT DISTINCT cruiseId FROM CruiseCabin)');
    }

    /**
     * @param Builder $query
     * @param $sourceName
     * @return Builder
     */
    public function scopeVendor(Builder $query, $sourceName, $shipVendorIds = [])
    {
        $whereShipIdsSQL = null;
        if(!empty($shipVendorIds)){
            $whereShipIdsSQL = ' OR shipId IN (SELECT id FROM Ship WHERE vendorId IN ('.implode(', ', $shipVendorIds).'))';
            return $query->whereRaw('(vendor = (SELECT id FROM CruiseSource WHERE prefix = ?) '.($whereShipIdsSQL).')',[$sourceName]);
        } else {
            return $query;
        }
    }

    /**
     * @param Builder $query
     * @param null $order
     * @param bool $asc
     * @return Builder
     */
    public function scopeOrderByColumn(Builder $query, $order = null, $asc = true) {
        switch ($order) {
            case 'id':
            case 'shipId':
            case 'directionId':
            case 'title':
            case 'departureDateTime':
            case 'returnDateTime':
            case 'departurePointId':
            case 'returnPointId':
            case 'countDays':
                return $asc ? $query->orderBy($order) : $query->orderByDesc($order);
                break;
            default:
                return $query->orderBy('departureDateTime');
                break;
        }
    }

    /**
     * @param Builder $query
     * @param ParameterBag $searchParams
     * @return Builder
     */
    public function scopeSearchByParams(Builder $query, ParameterBag $searchParams)
    {
        $monthFrom = $searchParams->get('monthFrom');
        $monthTo = $searchParams->get('monthTo');
        $months = $searchParams->get('months',[]);
        $pointsDeparture = $searchParams->get('departureCities',[]);
        $daysFrom = $searchParams->get('daysFrom');
        $daysTo = $searchParams->get('daysTo');
        $duration = $searchParams->get('duration',[]);
        $ships = $searchParams->get('ships',[]);
        $routeCities = $searchParams->get('routeCities',[]);

        $query->select('Cruise.*');
        $query->selectRaw('(DATEDIFF(`returnDateTime`,`departureDateTime`) + 1) AS `countDays`');

        if($months || $monthFrom || $monthTo){
            $query->selectRaw('MONTH(departureDateTime) as monthDeparture, MONTH(returnDateTime) as monthReturn');
            $query->selectRaw('YEAR(departureDateTime) as yearDeparture, YEAR(returnDateTime) as yearReturn');
            if($months){
                $sqlParts = [];
                $sqlParams = [];
                foreach ($months as $yearNum => $monthList) {
                    $yearNum = (int)$yearNum;
                    foreach ($monthList as $monthNum){
                        $monthNum = (int)$monthNum;

                        $sqlParts[] = '((yearDeparture = ? AND monthDeparture = ? ) OR (yearReturn = ? AND monthReturn = ?))';

                        $sqlParams[] = $yearNum;
                        $sqlParams[] = $monthNum;
                        $sqlParams[] = $yearNum;
                        $sqlParams[] = $monthNum;
                    }
                }

                $query->havingRaw('('.implode(' OR ', $sqlParts).')',$sqlParams);

            } else {
                if($monthFrom){
                    $monthFrom = (int)$monthFrom;
                    if($monthTo){
                        $monthTo = (int)$monthTo;
                        $query->havingRaw('((monthDeparture >= ? OR monthReturn >= ?) AND (monthDeparture <= ? OR monthReturn <= ?))',[$monthFrom,$monthFrom,$monthTo,$monthTo]);
                    } else {
                        $query->havingRaw('(monthDeparture >= ? OR monthReturn >= ?)',[(int)$monthFrom,(int)$monthFrom]);
                    }
                } else {
                    if($monthTo){
                        $monthTo = (int)$monthTo;
                        $query->havingRaw('(monthDeparture <= ? OR monthReturn <= ?)',[(int)$monthTo,(int)$monthTo]);
                    }
                }
            }
        }

        if($duration || $daysFrom || $daysTo){
            if($duration){
                $sqlParts = [];
                $sqlDays = [];
                foreach ($duration as $i => $days){
                    $sqlParts[] = 'countDays = ?';
                    $sqlDays[] = $days;
                }
                $query->havingRaw('('.implode(' OR ',$sqlParts).')', $sqlDays);
            } else {
                if($daysFrom){
                    if($daysTo){
                        $query->havingRaw('(countDays >= ? OR countDays <= ?)',[$daysFrom, $daysTo]);
                    } else {
                        $query->havingRaw('(countDays >= ?)',[$daysFrom]);
                    }
                } else {
                    if($daysTo){
                        $query->havingRaw('(countDays <= ?)',[$daysTo]);
                    }
                }
            }
        }

        if($ships){
            $query->whereIn('shipId',$ships);
        }

        if($pointsDeparture){
            $citiesIds = [];
            foreach ($pointsDeparture as $cityId){
                $citiesIds[] = (int)$cityId;
            }
            $citiesIds = array_unique($citiesIds);
            if(!empty($citiesIds)){
                $query->leftJoin('CruiseRoutePoint as CRP','CRP.id','=','Cruise.departurePointId');
                $query->whereRaw('CRP.cityId IN ('.implode(',', $citiesIds).')');
            }
        }

        if($routeCities){
            $routeCitiesIds = [];
            foreach ($routeCities as $cityId) {
                $routeCitiesIds[] = (int)$cityId;
            }
            if(!empty($routeCitiesIds)){
                $query->whereRaw(
                    'Cruise.id IN (SELECT DISTINCT CR.cruiseId 
                FROM `CruiseRoutePoint` AS `CRP` 
                JOIN `CruiseRoute` AS `CR` ON `CRP`.`id` = `CR`.`pointId` 
                WHERE `CRP`.`cityId` IN ('.implode(',',$routeCitiesIds).'))');
            }
        }
        return $query;
    }
}