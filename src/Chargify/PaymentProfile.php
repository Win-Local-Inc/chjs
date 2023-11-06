<?php

namespace WinLocalInc\Chjs\Chargify;

/**
 * @property int id
 * @property int expiration_year
 * @property int expiration_month
 * @property int customer_id
 * @property string card_type
 * @property string masked_card_number
 * @property string payment_type
 */
class PaymentProfile extends ObjectTypes implements ChargifyObject
{
    const OBJECT_NAME = 'payment_profile';
}
