<?php

namespace WinLocalInc\Chjs\Chargify;

/**
 * @property $price_point
 */
class PricePoints extends ObjectTypes implements ChargifyObject
{
    const OBJECT_NAME = 'price_points';

    const TO_COLLECTION = true;
}
