<?php

namespace App\Utility;


class Week
{
    /** @var int $year */
    private $year;

    /** @var int $week */
    private $week;

    public static function validateWeekCode($weekCode): bool
    {

    }

    /**
     * @param $weekCode
     * @return self
     */
    public static function newFromWeekCode($weekCode): self
    {

    }

    /**
     * @param \DateTime $time
     * @return self
     */
    public static function newFromDateTime(\DateTime $time): self
    {
        $week = new self(intval($time->format('o')), intval($time->format('W')));

        return $week;
    }

    public function __construct(int $year, int $week)
    {
        // TODO: Validate range
        if ($year < 1000 || $year > 3000) {
            throw new \RuntimeException('Invalid year, allowed range is 1000 - 3000');
        }

        if ($week < 0 || $week > 53) {
            throw new \RuntimeException('Invalid week, allowed range is 1 - 53');
        }

        $this->year = $year;
        $this->week = $week;

        return $this;
    }

    public function getCode(): string
    {
        return $this->year . '-' . $this->week;
    }

    public static function startTimeForWeekCode($weekCode, $timezone)
    {

    }

    public static function endTimeForWeekCode($weekCode, $timezone)
    {

    }
}