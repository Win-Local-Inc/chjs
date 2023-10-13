<?php

namespace WinLocalInc\Chjs\Enums;

enum ShareCardProPricing: string
{
    use EnumHelpers;
    case ZERO = 'sharecard_pro_zero';
    case MONTH = 'sharecard_pro_month';
    case BIANNUAL = 'sharecard_pro_biannual';
    case YEAR = 'sharecard_pro_year';
}
