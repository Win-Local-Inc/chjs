<?php

namespace WinLocalInc\Chjs\Exceptions;

use Exception;
use WinLocalInc\Chjs\Models\Subscription;

class SubscriptionUpdateFailure extends Exception
{
    public static function incompleteSubscription(Subscription $subscription): static
    {
        return new static(
            "The subscription \"{$subscription->subscription_id}\" cannot be updated because its payment is incomplete."
        );
    }

    public static function duplicatePrice(Subscription $subscription, $component): static
    {
        return new static(
            "The component \"$component\" is already attached to subscription \"{$subscription->subscription_id}\"."
        );
    }
}
