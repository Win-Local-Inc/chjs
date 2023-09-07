<?php

namespace Obsolete\Enums;

enum SubscriptionPaymentCollectionMethod: string
{
    case Automatic = 'automatic';
    case Remittance = 'remittance';
    case Prepaid = 'prepaid';
    case Invoice = 'invoice';
}
