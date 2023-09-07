<?php

namespace WinLocalInc\Chjs\Enums;

enum PaymentCollectionMethod: string
{
    case Automatic = 'automatic';
    case Remittance = 'remittance';
    case Prepaid = 'prepaid';
}
