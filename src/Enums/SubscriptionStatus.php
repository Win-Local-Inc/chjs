<?php

namespace WinLocalInc\Chjs\Enums;

enum SubscriptionStatus: string
{
    use EnumHelpers;
    case Active = 'active';
    case Canceled = 'canceled';
    case Expired = 'expired';
    case OnHold = 'on_hold';
    case PastDue = 'past_due';
    case Unpaid = 'unpaid';
    case Trial = 'trial';
    case OnGracePeriod = 'on_grace_period';

}
