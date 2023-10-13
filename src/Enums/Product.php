<?php

namespace WinLocalInc\Chjs\Enums;

enum Product: string
{
    use EnumHelpers;
    case PROMO = 'promo';
    case SOLO = 'solo';
    case ENTREPRENEUR = 'entrepreneur';
    case FRANCHISER = 'franchiser';
    case DISTRIBUTOR = 'distributor';
}
