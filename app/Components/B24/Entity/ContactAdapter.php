<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 20.04.17
 * Time: 14:04
 */

namespace App\Components\B24\Entity;

use App\Components\B24\Crm\Entity\Adapter;

/**
 * Class ContactAdapter
 * @package App\Components\B24\Entity
 */
class ContactAdapter extends Adapter
{
    protected static $fieldMap = [
        'ID' => 'id',
        'HONORIFIC' => 'honorific',
        'NAME' => 'name',//required
        'SECOND_NAME' => 'secondName',//required
        'LAST_NAME' => 'lastName',//required
        'PHOTO' => 'photo',//type:file
        'BIRTHDATE' => 'birthDate',//type:date
        'TYPE_ID' => 'typeId',//type:crm_status
        'SOURCE_ID' => 'sourceId',//type:crm_status
        'SOURCE_DESCRIPTION' => 'sourceDescription',
        'POST' => 'post',
        'ADDRESS' => 'address',
        'ADDRESS_2' => 'address2',
        'ADDRESS_CITY' => 'addressCity',
        'ADDRESS_POSTAL_CODE' => 'addressPostCode',
        'ADDRESS_REGION' => 'addressRegion',
        'ADDRESS_PROVINCE' => 'addressProvince',
        'ADDRESS_COUNTRY' => 'addressCountry',
        'ADDRESS_COUNTRY_CODE' => 'addressCountryCode',
        'COMMENTS' => 'comments',
        'OPENED' => 'opened',//type:char
        'EXPORT' => 'export',//type:char
        'HAS_PHONE' => 'hasPhone',//type:char
        'HAS_EMAIL' => 'hasEmail',//type:char
        'ASSIGNED_BY_ID' => 'assignedById',//type:user
        'CREATED_BY_ID' => 'createdById',//type:user
        'MODIFY_BY_ID' => 'modifyById',//type:user
        'DATE_CREATE' => 'dateCreate',//readonly
        'DATE_MODIFY' => 'dateModify',//readonly
        'COMPANY_ID' => 'companyId',//type:crm_company
        'COMPANY_IDS' => 'companyIds',//type:crm_company
        'LEAD_ID' => 'leadId',//type:crm_leads
        'ORIGINATOR_ID' => 'originatorId',
        'ORIGIN_ID' => 'originId',
        'ORIGIN_VERSION' => 'originVersion',
        'UTM_SOURCE' => 'utmSource',
        'UTM_MEDIUM' => 'utmMedium',
        'UTM_CAMPAIGN' => 'utmCampaign',
        'UTM_CONTENT' => 'utmContent',
        'UTM_TERM' => 'utmTerm',
        'PHONE' => 'phone',//type:crm_multifield
        'EMAIL' => 'email',//type:crm_multifield
        'WEB' => '',
        'IM' => '',
        'UF_CRM_1461679758' => 'adSource',//Рекламный канал, required
        'UF_CRM_1463436674' => 'passportSeries',
        'UF_CRM_1463346388' => 'passportNumber',
        'UF_CRM_1463436691' => 'passportSource',
        'UF_CRM_1463517623' => 'passportDate',
        'UF_CRM_5790DF052C38B' => 'roistat',
    ];

    protected static $fieldDefaults = [
        'sourceId' => 'WEB',//crm_status: 25: заявка с сайта
        'typeId' => 'CLIENT',//crm_status: 47: клиенты
        'adSource' => 52 //другое
    ];

    /**
     * @param $phone
     * @return array
     */
    protected function setPhoneAttribute($phone)
    {
        $phone = [
            [
                'VALUE_TYPE' => 'HOME',
                'VALUE' => $phone,
                //'TYPE_ID' => 'PHONE'
            ]
        ];
        //dd($phone);
        return $phone;
    }

    /**
     * @param $email
     * @return array
     */
    protected function setEmailAttribute($email)
    {
        return [
            [
                'VALUE_TYPE' => 'HOME',
                'VALUE' => $email,
                //'TYPE_ID' => 'EMAIL'
            ]
        ];
    }
}