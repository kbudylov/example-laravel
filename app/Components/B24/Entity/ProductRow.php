<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 22.04.17
 * Time: 20:24
 */

namespace App\Components\B24\Entity;

use App\Components\B24\Crm\Entity\Entity;

/**
 * Class Product
 * @package App\Components\B24\Entity
 */
class ProductRow extends Entity
{
    protected static $listUrl = 'crm.productrow.list.json';

    protected static $fieldsUrl = 'crm.productrow.fields.json';

    /**
     * @return ProductRowAdapter
     */
    public static function getAdapter()
    {
        return new ProductRowAdapter();
    }
}