<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 24.04.17
 * Time: 13:08
 */

namespace App\Components\B24\Entity;

use App\Components\B24\Crm\Entity\Entity;

/**
 * Class Product
 * @package App\Components\B24\Entity
 */
class Product extends Entity
{
    protected static $fieldsUrl = 'crm.product.fields';

    /**
     * @return ProductAdapter
     */
    public static function getAdapter()
    {
        return new ProductAdapter();
    }
}