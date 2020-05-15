<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 15.04.17
 * Time: 12:38
 */

namespace App\Components\B24\Crm\Entity;

use Illuminate\Support\Collection;

/**
 * Interface EntityInterface
 * @package App\Components\B24\Entity
 */
interface EntityInterface
{
    /**
     * EntityInterface constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = []);

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name);

    /**
     * @param $name
     * @param $value
     * @return mixed
     */
    public function __set($name, $value);

    /**
     * @param $name
     * @return mixed
     */
    public function __isset($name);

    /**
     * @param $id
     * @return static
     */
    public static function get($id);

    /**
     * @param $params array
     * @return Collection
     */
    public static function getList(array $params = []);

    /**
     * @return Collection
     */
    public static function fields();

    /**
     * @param array $attributes
     * @return static
     */
    public static function create(array $attributes = []);

    /**
     * @param array $attributes
     * @return bool
     */
    public function update(array $attributes = []);

    /**
     * @return bool
     */
    public function delete();

    /**
     * @return mixed
     */
    public function save();

    /**
     * @return array
     */
    public function getAttributes();

    /**
     * @param array $attributes
     */
    public function setAttributes(array $attributes = []);

    /**
     * @return array
     */
    public function getFields();

    /**
     * @param array $attributes
     */
    public function setFields(array $attributes);

    /**
     * @return AdapterInterface
     */
    public static function getAdapter();
}