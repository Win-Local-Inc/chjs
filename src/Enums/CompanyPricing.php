<?php

namespace WinLocalInc\Chjs\Enums;

enum CompanyPricing: string
{
    use EnumHelpers;
    case ZERO = 'company_zero';
    case MONTH = 'company_month';
    case BIANNUAL = 'company_biannual';
    case YEAR = 'company_year';
}
