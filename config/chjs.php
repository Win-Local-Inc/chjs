<?php

/*
 * You can place your custom package configuration in here.
 */
return [
    'hostname' => env('CHARGIFY_HOSTNAME'),
    'events_hostname' => env('CHARGIFY_EVENTS_HOSTNAME'),
    'subdomain' => env('CHARGIFY_SUBDOMAIN'),
    'api_key' => env('CHARGIFY_API_KEY'),
    'public_key' => env('CHARGIFY_PUBLIC_KEY'),
    'private_key' => env('CHARGIFY_PRIVATE_KEY'),
    'shared_key' => env('CHARGIFY_SHARED_KEY'),
    'timeout' => env('CHARGIFY_TIMEOUT', 10),
    'product_family_id' => env('CHARGIFY_PRODUCT_FAMILY_ID'),
    'webhook_queue' => env('CHARGIFY_WEBHOOK_QUEUE', 'redis'),

    'ads_component' => env('ADS_COMPONENT', 'ads'),
];
