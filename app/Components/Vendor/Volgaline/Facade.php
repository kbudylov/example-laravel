<?php
/**
 * Created by Konstantin Budylov.
 * Mailto: k.budylov@gmail.com
 * Date: 03.11.17 23:31
 **********************************************************************************/

namespace App\Components\Vendor\Volgaline;

/**
 * Class Volgaline
 * @package App\Components\Vendor\Volgaline
 */
class Facade extends \Illuminate\Support\Facades\Facade
{
	/**
	 * @inheritdoc
	 */
	protected static function getFacadeAccessor()
	{
		return 'volgaline';
	}
}