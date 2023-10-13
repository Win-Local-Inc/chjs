<?php

namespace WinLocalInc\Chjs\Enums;

enum DistributorPricing: string
{
    use EnumHelpers;
    case ZERO = 'distributor_zero';
    case MONTH = 'distributor_month';
    case BIANNUAL = 'distributor_biannual';
    case YEAR = 'distributor_year';
}
