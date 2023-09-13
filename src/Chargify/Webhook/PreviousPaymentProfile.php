<?php

namespace WinLocalInc\Chjs\Chargify\Webhook;

use WinLocalInc\Chjs\Chargify\ChargifyObject;
use WinLocalInc\Chjs\Chargify\ObjectTypes;

class PreviousPaymentProfile extends ObjectTypes implements ChargifyObject
{
    const OBJECT_NAME = 'previous_payment_profile';
}
