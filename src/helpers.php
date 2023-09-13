<?php

use WinLocalInc\Chjs\Chjs;

if (! function_exists('maxio')) {
    function maxio(): Chjs
    {
        return new Chjs(
            hostname: config('chjs.hostname'),
            apiKey: config('chjs.api_key'),
            timeout: config('chjs.timeout')
        );
    }
}
