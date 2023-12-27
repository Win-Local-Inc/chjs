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
    case PKG_PART_TIME = 'pkg_part_time';
    case PKG_FULL_TIME = 'pkg_full_time';
    case PKG_OVERTIME = 'pkg_overtime';
    case ONCE_OFF = 'once_off';
}
