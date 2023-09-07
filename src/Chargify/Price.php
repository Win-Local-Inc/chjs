<?php

namespace WinLocalInc\Chjs\Chargify;

class Price extends ObjectTypes implements ChargifyObject
{
    const OBJECT_NAME = 'prices';
    const TO_COLLECTION = TRUE;

    public function setAttribute($key, $value)
    {
        $this->$key =  match ($key) {
            'unit_price' => (int) number_format($value * 100, '0', '', ''),
            default => $value
        };
    }

}