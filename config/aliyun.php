<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | aliyun
    |--------------------------------------------------------------------------
    |
    | aliyunAccessKey
    | aliyunSecret
    |
    */

    'accessKey' => env('ALIYUN_ACCESS_KEY', ''),
    'secret' => env('ALIYUN_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | OSS settings
    |--------------------------------------------------------------------------
    |
    | Aliyun Oss Setting
    |
    */

    'oss' => [

        'bucket' => env('OSS_BUCKET'),

        'endpoint' => env('OSS_ENDPOINT'),
        'endpoint_internal' => env('OSS_ENDPOINT_INTERNAL'),

        'site_url' => env('OSS_SITE_URL'),
        'site_url_internal' => env('OSS_SITE_URL_INTERNAL'),

    ],
    'sms' => [
        'endpoint' => env('SMS_ENDPOINT'),
    ],
];
