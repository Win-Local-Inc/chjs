<?php

namespace WinLocalInc\Chjs\Enums;

enum SubscriptionInterval: string
{
    use EnumHelpers;
    case Month = 'month';
    case Quarterly = 'quarterly';
    case Biannual = 'biannual';
    case Trimester = 'trimester';
    case Year = 'year';

    public static function getIntervalUnit(int $interval): SubscriptionInterval
    {
        return match ($interval) {
            1 => SubscriptionInterval::Month,
            6 => SubscriptionInterval::Biannual,
            9 => SubscriptionInterval::Trimester,
            default => SubscriptionInterval::Year,
        };
    }

    public function getInterval(): int
    {
        return match ($this) {
            SubscriptionInterval::Month => 1,
            SubscriptionInterval::Quarterly => 3,
            SubscriptionInterval::Biannual => 6,
            SubscriptionInterval::Trimester => 9,
            default => 12,
        };
    }
}
