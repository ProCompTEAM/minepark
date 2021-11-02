<?php

namespace minepark\utils;

use DateTime;

class DateTimeUtility
{
    public static function parseDateTimeToString(DateTime $dateTime) : string
    {
        return $dateTime->format("Y-m-d H:i:s");
    }
}