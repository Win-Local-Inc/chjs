<?php

namespace WinLocalInc\Chjs\Chargify\Webhook;

use WinLocalInc\Chjs\Chargify\ChargifyObject;
use WinLocalInc\Chjs\Chargify\ObjectTypes;
use WinLocalInc\Chjs\Chargify\Product;

/**
 * @property Product product
 */
class Webhook extends ObjectTypes implements ChargifyObject
{
    const OBJECT_NAME = 'webhook';



}