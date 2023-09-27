<?php

use WinLocalInc\Chjs\Chjs;

if (! function_exists('maxio')) {
    function maxio(): Chjs
    {
        return resolve(Chjs::class);
    }
}
