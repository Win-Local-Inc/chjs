<?php

namespace WinLocalInc\Chjs\Enums;

enum FranchisePricing: string
{
    use EnumHelpers;
    case ZERO = 'franchise_zero';
    case MONTH = 'franchise_month';
    case BIANNUAL = 'franchise_biannual';
    case YEAR = 'franchise_year';
}
