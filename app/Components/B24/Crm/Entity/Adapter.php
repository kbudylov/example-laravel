<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 18.04.17
 * Time: 14:12
 */

namespace App\Components\B24\Crm\Entity;

/**
 * Class Adapter
 * @package App\Components\B24\Crm\Entity
 */
abstract class Adapter implements AdapterInterface
{
    /**
     * @var EntityInterface
     */
    protected $entity;

    /**
     * @var array
     */
    protected static $fieldMap = [];

    /**
     * @var array
     */
    protected static $fieldDefaults = [];

    /**
     * Adapter constructor.
     * @param EntityInterface $entity
     */
    public function __construct()
    {

    }

    /**
     * @inheritdoc
     */
    public function arrayToFields($array)
    {
        $fields = [];
        $array = array_merge(static::$fieldDefaults, $array);
        foreach ($array as $key => $value) {
            $fields[$this->mapKeyNameToFieldName($key)] = $this->getFieldValueForKey($key, $value);
        }
        //dd($fields);
        return $fields;
    }

    /**
     * @inheritdoc
     */
    public function fieldsToArray($fields)
    {
        $array = [];
        foreach ($fields as $field => $value) {
            $array[$this->mapFieldNameToKeyName($field)] = $this->getArrayValueForField($field, $value);
        }
        return $array;
    }

    /**
     * @inheritdoc
     */
    public function mapKeyNameToFieldName($key)
    {
        if(in_array($key, static::$fieldMap)){
            return array_search($key, static::$fieldMap);
        }
        return strtoupper($key);
    }

    /**
     * @inheritdoc
     */
    public function mapFieldNameToKeyName($field)
    {
        if(isset(static::$fieldMap[$field])){
            return static::$fieldMap[$field];
        } else {
            return strtolower($field);
        }
    }

    /**
     * @inheritdoc
     */
    public function getFieldValueForKey($key, $value)
    {
        $methodName = 'set'.ucfirst($key).'Attribute';
        return method_exists($this, $methodName) ? call_user_func(function($value) use ($methodName){
            return $this->$methodName($value);
        },$value) : $value;
    }

    /**
     * @inheritdoc
     */
    public function getArrayValueForField($field, $value)
    {
        $methodName = 'get'.ucfirst($this->mapFieldNameToKeyName($field)).'Attribute';
        return method_exists($this, $methodName) ? call_user_func(function($value) use ($methodName){
            return $this->$methodName($value);
        },$value) : $value;
    }
}