<?php

namespace WinLocalInc\Chjs\Enums;

enum SubscriptionStatus: string
{
    case Active = 'active';
    case Canceled = 'canceled';
    case Expired = 'expired';
    case OnHold = 'on_hold';
    case PastDue = 'past_due';
    case Unpaid = 'unpaid';

}
