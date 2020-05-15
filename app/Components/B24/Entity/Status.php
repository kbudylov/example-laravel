<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 21.04.17
 * Time: 15:19
 */

namespace App\Components\B24\Entity;

use App\Components\B24\Crm\Entity\Entity;

/**
 * Class Status
 * @package App\Components\B24\Entity
 */
class Status extends Entity
{
    protected static $listUrl = 'crm.status.list.json';

    public static function getAdapter()
    {
        return new StatusAdapter();
    }
}