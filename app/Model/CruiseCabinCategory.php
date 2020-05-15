<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CruiseCabinCategory
 * @package App\Model
 * @property int $id
 * @property int $cruiseId
 * @property int $categoryId
 * @property int $countAvailable
 */
class CruiseCabinCategory extends Model
{
    /**
     * @var string
     */
    protected $table = 'CruiseCabinCategory';

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
        'cruiseId','categoryId','countAvailable'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function category()
    {
        return $this->hasOne(ShipCabinCategory::class, 'id', 'categoryId');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function cruise()
    {
        return $this->hasOne(Cruise::class, 'id','cruiseId');
    }

    public function calculateCountAvailable()
    {
        $result = \DB::selectOne(' SELECT COUNT(*) as count
                                    FROM CruiseCabin C
                                    JOIN ShipCabin SC ON C.cabinId = SC.id
                                    WHERE 
                                        C.cruiseId = ? AND 
                                        SC.categoryId = ? AND 
                                        C.isAvailable = 1',[
                                            $this->cruiseId,
                                            $this->categoryId
                                ]);

        $this->countAvailable = !empty($result->count) ? $result->count : 0;
        $this->save();
    }
}
