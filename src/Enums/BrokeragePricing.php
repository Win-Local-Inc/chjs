<?php

namespace WinLocalInc\Chjs\Enums;

enum BrokeragePricing: string
{
    use EnumHelpers;
    case ZERO = 'brokerage_zero';
    case MONTH = 'brokerage_month';
    case BIANNUAL = 'brokerage_biannual';
    case YEAR = 'brokerage_year';
}
