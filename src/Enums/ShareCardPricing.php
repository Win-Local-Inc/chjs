<?php

namespace WinLocalInc\Chjs\Enums;

enum ShareCardPricing: string
{
    use EnumHelpers;
    case ZERO = 'sharecard_zero';
    case MONTH = 'sharecard_month';
    case BIANNUAL = 'sharecard_biannual';
    case YEAR = 'sharecard_year';
}
