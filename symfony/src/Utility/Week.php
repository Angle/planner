<?php

namespace App\Utility;


class Week
{
    const DEFAULT_TIMEZONE = 'America/Monterrey';

    /** @var int $year */
    private $year;

    /** @var int $week */
    private $week;

    public static function validateWeekCode(string $weekCode): bool
    {
        try {
            $week = self::newFromWeekCode($weekCode);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param $weekCode
     * @return self
     */
    public static function newFromWeekCode($weekCode): self
    {
        $parts = explode('-', $weekCode);

        if (count($parts) != 2) {
            throw new \RuntimeException('Invalid week code format, expecting YYYY-WW');
        }

        if (!is_numeric($parts[0]) || !is_numeric($parts[1])) {
            throw new \RuntimeException('Invalid week code format, expecting YYYY-WW');
        }

        $week = new self(intval($parts[0]), intval($parts[1]));
        return $week;
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

    public function getPreviousWeek(): Week
    {
        $date = $this->getStartDay();
        $date->sub(new \DateInterval('P7D'));
        return self::newFromDateTime($date);
    }

    public function getNextWeek(): Week
    {
        $date = $this->getStartDay();
        $date->add(new \DateInterval('P7D'));
        return self::newFromDateTime($date);
    }

    /**
     * Check if THIS week is older than the other given week
     * @param Week $otherWeek
     * @return bool
     */
    public function isOlderThanWeek(Week $otherWeek): bool
    {
        if ($this->year < $otherWeek->getYear()) {
            return true;
        } elseif ($this->year == $otherWeek->getYear()) {
            if ($this->week < $otherWeek->getWeek()) {
                return true;
            }
        }

        return false;
    }

    // GETTERS AND SETTERS
    public function getCode(): string
    {
        return $this->year . '-' . $this->week;
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function getWeek(): int
    {
        return $this->week;
    }

    public function getStartDay(): \DateTime
    {
        $tz = new \DateTimeZone(self::DEFAULT_TIMEZONE);
        // 1 Monday
        $date = new \DateTime('now', $tz);
        $date->setISODate($this->year, $this->week, 1);
        return $date;
    }

    public function getEndDay(): \DateTime
    {
        $tz = new \DateTimeZone(self::DEFAULT_TIMEZONE);
        // 7 Sunday
        $date = new \DateTime('now', $tz);
        $date->setISODate($this->year, $this->week, 7);
        return $date;
    }
}