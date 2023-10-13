<?php

namespace WinLocalInc\Chjs\Chargify;

/**
 * @property $id
 * @property $state
 * @property $payment_collection_method
 * @property $total_revenue_in_cents
 * @property $product_price_in_cents
 * @property $next_assessment_at
 * @property $created_at
 * @property $updated_at
 */
class Subscription extends ObjectTypes implements ChargifyObject
{
    const OBJECT_NAME = 'subscription';
}
