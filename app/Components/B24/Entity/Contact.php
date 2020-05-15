<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 20.04.17
 * Time: 14:03
 */

namespace App\Components\B24\Entity;

use App\Components\B24\Crm\Entity\Entity;

/**
 * Class Contact
 * @package App\Components\B24\Entity
 */
class Contact extends Entity
{
    /**
     * @var string
     */
    protected static $createUrl = 'crm.contact.add.json';

    /**
     * @var string
     */
    protected static $listUrl = 'crm.contact.list.json';

    /**
     * @var string
     */
    protected static $getUrl = 'crm.contact.get.json';

    /**
     * @var string
     */
    protected static $fieldsUrl = 'crm.contact.fields';


    public static function getAdapter()
    {
        return new ContactAdapter();
    }
}