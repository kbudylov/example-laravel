<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 09.04.17
 * Time: 0:49
 */

return [
    'enableCrmIntegration' => env('B24_ENABLE_CRM_INTEGRATION',false),
    'domain' => env('B24_DOMAIN'),
    'clientId' => env('B24_CLIENT_ID'),
    'clientSecret' => env('B24_CLIENT_SECRET'),
    'redirectUrl' => env('B24_REDIRECT_URL'),
    'login' => 'noreply-volgaline@yandex.ru',
    'password' => 'adminka1209',
    'crmSyncDealDeleteAppKey' => 'qaehieodrk012ilzjauvm4h88v7vmnf4',
    'crmSyncDealUpdateStatusAppKey' => 'cxbuta7mr9we7l3ho3sc4bkps7ug6ail',
    'dealCreateQueue' => 'b24',
    'requestLog' => base_path('storage/logs/b24.request.log'),
    'hookLog' => base_path('storage/logs/b24.hook.log')
];
