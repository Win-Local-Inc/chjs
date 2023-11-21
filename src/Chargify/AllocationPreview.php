<?php

namespace WinLocalInc\Chjs\Chargify;

/**
 * @property int total_in_cents
 * @property int subtotal_in_cents
 * @property int existing_balance_in_cents
 * @property int total_discount_in_cents
 * @property int total_tax_in_cents
 */
class AllocationPreview extends ObjectTypes implements ChargifyObject
{
    const OBJECT_NAME = 'allocation_preview';
}
