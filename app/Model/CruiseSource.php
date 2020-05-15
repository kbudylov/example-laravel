<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 16.05.17
 * Time: 15:19
 */

namespace App\Model;

use App\Exceptions\InvalidConfigException;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CruiseSource
 * @package App\Model
 * @property string $name
 * @property string $prefix
 * @property int triesCount
 * @property string $pingUrl
 * @property string $baseUrl
 * @property string $helpUrl
 * @property string $className
 * @property bool $isActive
 * @property bool $isEnabled
 * @property bool $isInvalid
 * @property bool $isRunning
 * @property array $config
 */
class CruiseSource extends Model
{
    /**
     * @var string
     */
    protected $table = 'CruiseSource';

	/**
	 * @param $prefix
	 *
	 * @return \Illuminate\Database\Eloquent\Model|null|static
	 */
    public static function findByPrefix($prefix)
    {
	    return static::where(['prefix' => $prefix])->first();
    }

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
    public function cruises()
    {
    	return $this->hasMany(Cruise::class,'vendor','id');
    }

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
    public function ships()
    {
    	return $this->hasMany(Ship::class, 'vendor','id');
    }

	/**
	 * @param array $ids
	 *
	 * @return mixed|void
	 */
    public function deleteCruisesNotIn(array $ids = [])
    {
    	if (empty($ids)) {
    		return;
	    }
    	return $this->cruises()->whereNotIn([
    		'id' => $ids
	    ])->delete();
    }

    /**
     * @param Cruise $cruise
     * @return \App\Components\Vendor\FactoryInterface
     * @throws InvalidConfigException
     */
    public function getFactory(Cruise $cruise)
    {
        $classname = config('vendor.'.$this->prefix.'.factory.class');
        if ($classname) {
            return new $classname($cruise);
        } else {
            throw new InvalidConfigException('Configuration option [vendor.'.$this->prefix.'.factory.class] is undefined');
        }
    }
}