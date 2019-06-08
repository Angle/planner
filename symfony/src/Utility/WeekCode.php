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

}