<?php

namespace App\Utility;


abstract class WeekCode
{
    public static function validateWeekCode($weekCode): bool
    {

    }

    /**
     * @param $weekCode
     * @return array [year: int, week: int]
     */
    public static function getYearAndWeekFromWeekCode($weekCode): array
    {

    }

    public static function startTimeForWeekCode($weekCode, $timezone)
    {

    }

    public static function endTimeForWeekCode($weekCode, $timezone)
    {

    }

    public static function strpad($number, $pad_length, $pad_string) {
        return str_pad($number, $pad_length, $pad_string, STR_PAD_LEFT);
    }

}