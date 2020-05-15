<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 18.04.17
 * Time: 14:12
 */

namespace App\Components\B24\Crm\Entity;

/**
 * Interface AdapterInterface
 * @package App\Components\B24\Crm\Entity
 */
interface AdapterInterface
{
    /**
     * AdapterInterface constructor.
     */
    public function __construct();

    /**
     * @param $array array
     * @return array
     */
    public function arrayToFields($array);

    /**
     * @param $fields array
     * @return array
     */
    public function fieldsToArray($fields);

    /**
     * @param $key string
     * @return string|null
     */
    public function mapKeyNameToFieldName($key);

    /**
     * @param $field string
     * @return string|null
     */
    public function mapFieldNameToKeyName($field);

    /**
     * @param $key string
     * @param $value mixed
     * @return mixed
     */
    public function getFieldValueForKey($key, $value);

    /**
     * @param $field string
     * @param $value mixed
     * @return mixed
     */
    public function getArrayValueForField($field, $value);
}