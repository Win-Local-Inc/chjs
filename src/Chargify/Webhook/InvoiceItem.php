<?php

namespace WinLocalInc\Chjs\Chargify\Webhook;

use WinLocalInc\Chjs\Chargify\ChargifyObject;
use WinLocalInc\Chjs\Chargify\ObjectTypes;

class InvoiceItem extends ObjectTypes implements ChargifyObject
{
    const OBJECT_NAME = 'line_items';
}
