<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 22.04.17
 * Time: 20:25
 */

namespace App\Components\B24\Entity;

use App\Components\B24\Crm\Entity\Adapter;

/**
 * Class ProductAdapter
 * @package App\Components\B24\Entity
 */
class ProductRowAdapter extends Adapter
{
    protected static $fieldMap = [
        "ID" => "id",
        "OWNER_ID" => "ownerId",//+"type": "integer" +"isRequired": true
        "OWNER_TYPE" => "ownerType",//"type": "string" "isRequired": true
        "PRODUCT_ID" => "productId",//+"type": "integer" "isRequired": true
        "PRODUCT_NAME" => "productName",//"type": "string" "isRequired": false
        "PRICE" => "price", //+"type": "double"+"isRequired": false
        "PRICE_EXCLUSIVE" => "priceExclusive", //"type": "double"+"isRequired": false
        "PRICE_NETTO" => "priceNetto",
        "PRICE_BRUTTO" => "priceBrutto",
        "QUANTITY" => "quantity", //+"type": "double"+"isRequired": false
        "DISCOUNT_TYPE_ID" => "discountTypeId", //+"type": "integer"+"isRequired": false
        "DISCOUNT_RATE" => "discountRate", //"type": "double"+"isRequired": false
        "DISCOUNT_SUM" => "discountSum", //"type": "double"+"isRequired": false
        "TAX_RATE" => "taxRate", //+"type": "double"+"isRequired": false
        "TAX_INCLUDED" => "taxIncluded",//+"type": "char"+"isRequired": false
        "CUSTOMIZED" => "customized", //+"type": "char"+"isRequired": false
        "MEASURE_CODE" => "measureCode", //+"type": "integer"+"isRequired": false
        "MEASURE_NAME" => "measureName", //+"type": "string"
        "SORT" => "sort", //+"type": "integer"+"isRequired": false
    ];
}