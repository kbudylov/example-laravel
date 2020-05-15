<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ShipCabinCategoryPhoto
 * @package App\Model
 */
class ShipCabinCategoryPhoto extends Model
{
    /**
     * @var string
     */
    protected $table = 'ShipCabinCategoryPhoto';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var array
     */
    protected $fillable = [
        'categoryId','url'
    ];

    public static function findAllByCategoryId($categoryId)
    {
        return static::where(['categoryId' => $categoryId])->get();
    }
}
