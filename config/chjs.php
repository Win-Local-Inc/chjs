<?php

/*
 * You can place your custom package configuration in here.
 */
return [
    'hostname' => env('CHARGIFY_HOSTNAME', 'https://win-local-dev.chargify.com/'),
    'events_hostname' => env('CHARGIFY_EVENTS_HOSTNAME', 'https://events.chargify.com/'),
    'subdomain' => env('CHARGIFY_SUBDOMAIN', 'win-local-dev'),
    'api_key' => env('CHARGIFY_API_KEY', 'V81adFgtnAboTUmBc0wj9250bbFOHvhlGEk2YI4'),
    'public_key' => env('CHARGIFY_PUBLIC_KEY', 'chjs_9dx8x9zjy5qk9y8v8szbbfnb'),
    'private_key' => env('CHARGIFY_PRIVATE_KEY', 'chjs_pvt_sdqgmdwpgr6jb7h9wzg2qz89'),
    'shared_key' => env('CHARGIFY_SHARED_KEY', 'Aq6BZ6FN3BwOuqFWTdaWnPjmUVfJad4uAlgzamRHv4'),
    'timeout'=> env('CHARGIFY_TIMEOUT', 10),
];
