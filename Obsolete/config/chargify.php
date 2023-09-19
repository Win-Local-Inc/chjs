<?php

return [
    'hostname' => env('CHARGIFY_HOSTNAME'),
    'eventsHostname' => env('CHARGIFY_EVENTS_HOSTNAME'),
    'subdomain' => env('CHARGIFY_SUBDOMAIN'),
    'apiKey' => env('CHARGIFY_API_KEY'),
    'publicKey' => env('CHARGIFY_PUBLIC_KEY'),
    'privateKey' => env('CHARGIFY_PRIVATE_KEY'),
    'sharedKey' => env('CHARGIFY_SHARED_KEY'),
    'timeout' => env('CHARGIFY_TIMEOUT', 120),
];
