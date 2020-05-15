<?php
/**
 * Created by Konstantin Budylov.
 * Mailto: k.budylov@gmail.com
 * Date: 03.11.17 23:31
 **********************************************************************************/

namespace App\Components\Vendor\Infoflot;

/**
 * Class Facade
 * @package App\Components\Vendor\Infoflot
 */
class Facade extends \Illuminate\Support\Facades\Facade
{
	/**
	 * @inheritdoc
	 */
	protected static function getFacadeAccessor()
	{
		return 'infoflot';
	}
}