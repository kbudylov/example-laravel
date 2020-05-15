<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 05.04.17
 * Time: 23:03
 */

namespace App\Components;
use Illuminate\Support\Collection;

/**
 * Class ArrayHelper
 * @package App\Components
 */
class ArrayPath
{
    /**
     * Delimiter for key path
     */
    const PATH_DELIMITER = '.';

    /**
     * @param string $keyPath
     * @param object|array $source
     * @return mixed
     */
    public static function getByPath($keyPath, $source)
    {
        $ptr = $source;
        $pathParts = self::splitPath($keyPath);
        try {
            foreach ($pathParts as $key){
                $ptr = self::getPtr($key, $ptr);
            }
            return $ptr;
        } catch (\Exception $e) {
            throw new \RuntimeException('Path error: '.$e->getMessage());
        }
    }

    /**
     * @param string $keyPath
     * @param mixed $value
     * @param array|object $source
     */
    public static function setByPath($keyPath, $value, array &$source)
    {
        $ptr = &$source;
        $pathParts = self::splitPath($keyPath);
        foreach ($pathParts as $key){
            if(!isset($ptr[$key])){
                $ptr[$key] = [];
            }
            $ptr = &$ptr[$key];
        }
        $ptr = $value;
    }

    /**
     * @param $keyPath
     * @return array
     */
    protected static function splitPath($keyPath)
    {
        $path = preg_split('/\./', $keyPath, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($path as $k => $key){
            if((string)(int)$key === $key){
                $path[$k] = (int)$key;
            }
        }
        return $path;
    }

    /**
     * @param string $key
     * @param array|object $source
     * @param bool $keyCreate
     * @return mixed
     * @throws \RuntimeException
     */
    protected static function getPtr($key, &$source, $keyCreate = false)
    {
        if(is_object($source)){
            if($source instanceof Collection){
                if($source->offsetExists($key)){
                    return $source->get($key);
                } else {
                    if($keyCreate){
                        $source->set($key, new Collection());
                        return $source->get($key);
                    } else {
                        throw new \RuntimeException('Source key ['.$key.'] is undefined');
                    }
                }
            } else {
                if(isset($source->$key)){
                    return $source->$key;
                } else {
                    if($keyCreate){
                        $source->$key = new \StdClass();
                        return $source->$key;
                    } else {
                        throw new \RuntimeException('Source key ['.$key.'] is undefined');
                    }
                }
            }
        } elseif(is_array($source)) {
            if(isset($source[$key])){
                return $source[$key];
            } else {
                if($keyCreate){
                    $source[$key] = [];
                    return $source->$key;
                } else {
                    throw new \RuntimeException('Source key ['.$key.'] is undefined');
                }
            }
        } else {
            throw new \RuntimeException('Unexpected source type ('.gettype($source).'), expecting array|object');
        }
    }


}