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
    case AD_RECURRING = 'ad_recurring';
    case MONTHLY_CONTENT_MANAGEMENT = 'monthly-content-management';

    public function isParentable(): bool
    {
        return in_array($this, [
            self::ENTREPRENEUR,
            self::FRANCHISER,
            self::ENTREPRENEUR,
            self::PKG_PART_TIME,
            self::PKG_FULL_TIME,
            self::PKG_OVERTIME
        ]);
    }

    public function isEntrepreneurial(): bool
    {
        return in_array($this, [
            self::ENTREPRENEUR,
            self::PKG_PART_TIME,
            self::PKG_FULL_TIME,
            self::PKG_OVERTIME,
        ]);
    }
}
