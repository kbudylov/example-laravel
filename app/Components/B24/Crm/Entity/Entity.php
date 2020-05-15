<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 08.04.17
 * Time: 20:57
 */

namespace App\Components\B24\Crm\Entity;

use App\Components\B24\Http\Client;
use App\Exceptions\InvalidArgumentException;

/**
 * Class Entity
 * @package App\Components\B24\Entity
 */
abstract class Entity implements EntityInterface
{
    /**
     * @var string|null
     */
    protected static $createUrl;

    /**
     * @var string
     */
    protected static $listUrl;

    /**
     * @var string
     */
    protected static $getUrl;

    /**
     * @var string
     */
    protected static $fieldsUrl;

    /**
     * @var string
     */
    protected static $updateUrl;

    /**
     * @var string
     */
    protected static $deleteUrl;

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @inheritdoc
     */
    public function __construct(array $attributes = [])
    {
        if(!empty($attributes)){
            $this->setAttributes($attributes);
        }
    }

    /**
     * @inheritdoc
     */
    public function __get($key)
    {
        $fieldName = static::getAdapter()->mapKeyNameToFieldName($key);
        if(isset($this->attributes[$fieldName])){
            return static::getAdapter()->getArrayValueForField($fieldName, $this->attributes[$fieldName]);
        } else {
            throw new \RuntimeException("Attribute {$key} is undefined");
        }
    }

    /**
     * @inheritdoc
     */
    public function __set($key, $value)
    {
        $fieldName = static::getAdapter()->mapKeyNameToFieldName($key);
        if($fieldName){
            $this->attributes[$fieldName] = static::getAdapter()->getFieldValueForKey($key, $value);
        } else {
            throw new \RuntimeException("Attribute {$key} is undefined");
        }
    }

    /**
     * @inheritdoc
     */
    public function __isset($key)
    {
        $fieldName = static::getAdapter()->mapKeyNameToFieldName($key);
        return isset($this->attributes[$fieldName]);
    }

    /**
     * @inheritdoc
     */
    public function getAttributes()
    {
        $array = static::getAdapter()->fieldsToArray($this->attributes);
        //ksort($array);
        return $array;
    }

    /**
     * @inheritdoc
     */
    public function getFields()
    {
        return $this->attributes;
    }

    /**
     * @inheritdoc
     */
    public function setFields(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @inheritdoc
     */
    public function setAttributes(array $attributes = [])
    {
        $this->attributes = static::getAdapter()->arrayToFields(collect($this->attributes)->merge($attributes)->toArray());
    }

    /**
     * @inheritdoc
     */
    public static function getList(array $params = [])
    {
        if (!empty(static::$listUrl)) {
            $client = Client::getInstance();
            if(!empty($params)){
                $result = \GuzzleHttp\json_decode($client -> crmQuery(static::$listUrl,$params));
            } else {
                $result = \GuzzleHttp\json_decode($client -> crmQuery(static::$listUrl));
            }
            if(isset($result->result) && !empty($result->result)){
                $collection = collect([]);
                foreach ($result->result as $k => $entityData){
                    $collection->put($k, self::instance($entityData));
                }
                return $collection;
            } else {
                return collect([]);
            }
        } else {
            throw new \RuntimeException('URI for list entity is undefined');
        }
    }

    /**
     * @inheritdoc
     */
    public static function fields()
    {
        if (!empty(static::$fieldsUrl)) {
            $client = Client::getInstance();
            $result = \GuzzleHttp\json_decode($client -> crmQuery(static::$fieldsUrl));
            if(isset($result->result) && !empty($result->result)){
                $collection = collect([]);
                foreach ($result->result as $k => $item){
                    $collection->put($k, $item);
                }
                return $collection;
            } else {
                return collect([]);
            }
        } else {
            throw new \RuntimeException('URI for list entity fields is undefined');
        }
    }

    /**
     * @inheritdoc
     */
    public static function create(array $attributes = [])
    {
        if (!empty(static::$createUrl)) {
            $client = Client::getInstance();
            $fields = static::getAdapter()->arrayToFields($attributes);
            //dd($fields);
            $result = \GuzzleHttp\json_decode($client -> crmQuery(static::$createUrl, [
                'fields' => $fields
            ]));
            if(!empty($result->result)){
                return static::get($result->result);
            } else {
                throw new \RuntimeException('Undefined ID in the query result');
            }
        } else {
            throw new \RuntimeException('URI for create entity is undefined');
        }
    }

    /**
     * @inheritdoc
     */
    public static function get($id)
    {
        if (!empty(static::$getUrl)) {
            $result = Client::getInstance()->crmQuery(static::$getUrl,[
                'id' => $id
            ]);
            if ($result) {
                $result = \GuzzleHttp\json_decode($result);
                if(!empty($result->result)){
                    return self::instance($result->result);
                } else {
                    return null;
                    //throw new \RuntimeException('Result is undefined');
                }
            } else {
                return null;
            }
        } else {
            throw new \RuntimeException('URI for get entity is undefined');
        }
    }

    /**
     * @inheritdoc
     */
    public function update(array $attributes = [])
    {
        //return $this->save();
    }

    /**
     * @inheritdoc
     */
    public function delete()
    {
        //return true;
    }

    /**
     * @inheritdoc
     */
    public function save()
    {
        if($this->id){
            $client = Client::getInstance();
            $fields = static::getAdapter()->arrayToFields($this->getAttributes());
            //dd($fields);
            $result = \GuzzleHttp\json_decode($client -> crmQuery(static::$updateUrl, [
                'id' => $this->id,
                'fields' => $fields
            ]));
            if($result){
                return true;
            }
        } else {
            throw new \RuntimeException('Deal is new, please use Deal::create for create new deals');
        }
    }

    /**
     * @param array $attributes
     * @return static
     * @throws InvalidArgumentException
     */
    protected static function instance($attributes = [])
    {
        if(is_object($attributes)){
            $attrArray = [];
            foreach ($attributes as $k => $v) {
                $attrArray[$k] = $v;
            }
        } elseif(is_array($attributes)) {
            $attrArray = $attributes;
        } else {
            throw new InvalidArgumentException('Unexpected argument type: '.gettype($attributes).', expecting array|object');
        }
        $entity = new static();
        $entity -> setCleanAttributes($attrArray);
        return $entity;
    }

    /**
     * @param $attributes array
     */
    protected function setCleanAttributes($attributes)
    {
        $this->attributes = $attributes;
    }
}