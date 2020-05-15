<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CruiseDirection
 * @package App\Model
 */
class CruiseDirection extends Model
{
    /**
     * @var string
     */
    protected $table = 'CruiseDirection';

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
        'title','originalName','vendor','vendorId'
    ];
}
