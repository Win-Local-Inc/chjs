<?php

namespace WinLocalInc\Chjs\Chargify;

/**
 * @property $price_point
 * @property $component_id
 * @property $prices
 */
class PricePoints extends ObjectTypes implements ChargifyObject
{
    const OBJECT_NAME = 'price_points';

    const TO_COLLECTION = true;
}
