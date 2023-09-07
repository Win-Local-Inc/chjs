<?php

use WinLocalInc\Chjs\Chjs;
use WinLocalInc\Chjs\Services\CustomerService;
use WinLocalInc\Chjs\Services\SubscriptionService;


if( !function_exists( 'maxio' )) {
    function maxio(): Chjs
    {
        return  new Chjs(
            hostname: config('chjs.hostname'),
            apiKey: config('chjs.api_key'),
            timeout: config('chjs.timeout')
        );
    }
}