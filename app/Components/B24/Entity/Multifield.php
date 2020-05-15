<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 21.04.17
 * Time: 16:12
 */

namespace App\Components\B24\Entity;

use App\Components\B24\Crm\Entity\Entity;

/**
 * Class Multifield
 * @package App\Components\B24\Entity
 */
class Multifield extends Entity
{
    protected static $fieldsUrl = 'crm.multifield.fields.json';

    public static function getAdapter()
    {
        // TODO: Implement getAdapter() method.
    }
}