<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 04.12.17
 * Time: 17:13
 */

namespace App\Components\Vendor;

use App\Model\Cruise;

/**
 * Interface VendorInterface
 * @package App\Components\Vendor
 */
interface VendorInterface
{
    /**
     * @param Cruise $cruise
     * @return mixed
     */
    public function getFactory(Cruise $cruise);
}