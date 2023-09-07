<?php

namespace WinLocalInc\Chjs\Chargify;

class Component extends ObjectTypes implements ChargifyObject
{
    const OBJECT_NAME = 'component';

//
    public function setAttribute($key, $value)
    {
        $this->$key =  match ($key) {
            'allocated_quantity' => is_int($value) ? $value :
                (int) number_format($value, '0', '', ''),
            default => $value
        };
    }
}