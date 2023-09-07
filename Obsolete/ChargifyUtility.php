<?php

namespace Obsolete;

use Illuminate\Support\Facades\Date;

class ChargifyUtility
{
    public static function getFixedDateTime(string $dateTime = null): ?string
    {
        $matches = [];
        if ($dateTime && preg_match('/(\d{4}-\d{2}-\d{2})[^\d]*(\d{2}\:\d{2}:\d{2})[^\d-]*(-?\d{2}:?\d{2})/', $dateTime, $matches)) {
            $date = Date::createFromFormat('Y-m-d_H:i:s', $matches[1].'_'.$matches[2], $matches[3]);
            $date->setTimezone(new \DateTimeZone('UTC'));

            return $date->toDateTimeString();
        }

        return null;
    }
}
