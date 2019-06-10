<?php

namespace App\Entity;

use DateTime;

use Doctrine\ORM\Mapping as ORM;

use Angle\Common\Utilities\Random\Random;

use App\Util\Week;


/**
 * @ORM\Entity(repositoryClass="App\Repository\TaskRepository")
 * @ORM\Table(name="Tasks")
 * @ORM\HasLifecycleCallbacks()
 */
class Task
{
    #########################
    ##        PRESETS      ##
    #########################

    // TODO: Dynamic timezones per user
    const DEFAULT_TIMEZONE = 'America/Monterrey';

    const CODE_LENGTH = 32;

    const STATUS_OPEN = 1;
    const STATUS_CLOSED = 2;
    const STATUS_CANCELLED = 3;

    const BULLET_DOT = 1;
    const BULLET_TIMES = 2;
    const BULLET_ARROW = 3;
    const BULLET_DASH = 4;
    const BULLET_UNKNOWN = 99;

    const FLAG_NONE = 1;
    const FLAG_DASH = 2;
    const FLAG_UNKNOWN = 99;


    #########################
    ##      PROPERTIES     ##
    #########################

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $taskId;

    /**
     * @ORM\Column(type="string", unique=true)
     */
    private $code;

    /**
     * @ORM\Column(type="string")
     */
    private $concept;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $details;

    /**
     * @ORM\Column(type="smallint")
     */
    private $openWeekNumber;

    /**
     * @ORM\Column(type="smallint")
     */
    private $openYearNumber;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $closeWeekNumber;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $closeYearNumber;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $cancelWeekNumber;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $cancelYearNumber;


    #########################
    ## OBJECT RELATIONSHIP ##
    #########################

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Notebook", inversedBy="tasks")
     * @ORM\JoinColumn(referencedColumnName="notebook_id", nullable=false)
     */
    private $notebook;


    #########################
    ##     CONSTRUCTOR     ##
    #########################

    public function __construct()
    {
        $this->code = Random::generateString(self::CODE_LENGTH);

        $tz = new \DateTimeZone(self::DEFAULT_TIMEZONE);
        $this->setOpenWeekFromTimestamp(new \DateTime('now', $tz));
    }


    #########################
    ##   SPECIAL METHODS   ##
    #########################

    public function getOpenWeek(): ?Week
    {
        if ($this->openYearNumber && $this->openWeekNumber) {
            return new Week($this->openYearNumber, $this->openWeekNumber);
        } else {
            return null;
        }
    }

    public function getCloseWeek(): ?Week
    {
        if ($this->closeYearNumber && $this->closeWeekNumber) {
            return new Week($this->closeYearNumber, $this->closeWeekNumber);
        } else {
            return null;
        }
    }

    public function getCancelWeek(): ?Week
    {
        if ($this->cancelYearNumber && $this->cancelWeekNumber) {
            return new Week($this->cancelYearNumber, $this->cancelWeekNumber);
        } else {
            return null;
        }
    }

    public function getStatus(): int
    {
        if (!$this->getCancelWeek() && !$this->getCloseWeek()) {
            return self::STATUS_OPEN;
        } elseif ($this->getCloseWeek()) {
            return self::STATUS_CLOSED;
        } elseif ($this->getCancelWeek()) {
            return self::STATUS_CANCELLED;
        }

        throw new \RuntimeException('Unknown Task status');
    }

    public function getBullet(Week $queryWeek, Week $currentWeek): int
    {
        if ($this->getStatus() == self::STATUS_OPEN) {
            // TASK IS STILL OPEN
            // The task is not yet closed, we have to determine if we show a dot or an arrow
            if ($queryWeek->equals($currentWeek) || $queryWeek->isNewerThanWeek($currentWeek)) {
                return self::BULLET_DOT;
            } else {
                return self::BULLET_ARROW;
            }
        } elseif ($this->getStatus() == self::STATUS_CLOSED) {
            // TASK HAS BEEN CLOSED
            // the task has been closed, we have to determine if we show a times or an arrow
            if ($queryWeek->equals($this->getCloseWeek())) {
                return self::BULLET_TIMES;
            } else {
                return self::BULLET_ARROW;
            }
        } elseif ($this->getStatus() == self::STATUS_CANCELLED) {
            // TASK HAS BEEN CANCELLED
            // the task has been cancelled, we have to determine if we strike if or we show an arrow
            if ($queryWeek->equals($this->getCancelWeek())) {
                return self::BULLET_DASH;
            } else {
                return self::BULLET_ARROW;
            }
        }

        return self::BULLET_UNKNOWN;
    }

    public function getFlag(Week $queryWeek, Week $currentWeek): int
    {
        if ($this->getOpenWeek()->isOlderThanWeek($queryWeek)) {
            return self::FLAG_DASH;
        }

        return self::FLAG_NONE;
    }

    public function getStrike(Week $queryWeek, Week $currentWeek): bool
    {
        // Only strike the text if the task was cancelled on the current week
        if ($this->getStatus() == self::STATUS_CANCELLED) {
            if ($queryWeek->equals($this->getCancelWeek())) {
                return true;
            }
        }

        return false;
    }


    public function setOpenWeekFromTimestamp(\DateTime $time)
    {
        $week = Week::newFromDateTime($time);
        $this->openWeekNumber = $week->getWeek();
        $this->openYearNumber = $week->getYear();
    }


    #########################
    ## GETTERS AND SETTERS ##
    #########################

    /**
     * @return int|null
     */
    public function getTaskId(): ?int
    {
        return $this->taskId;
    }

    /**
     * @return string|null
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return Task
     */
    public function setCode(string $code): self
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getConcept(): ?string
    {
        return $this->concept;
    }

    /**
     * @param string $concept
     * @return Task
     */
    public function setConcept(string $concept): self
    {
        $this->concept = $concept;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDetails(): ?string
    {
        return $this->details;
    }

    /**
     * @param string $details
     * @return Task
     */
    public function setDetails(string $details): self
    {
        $this->details = $details;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getOpenWeekNumber(): ?int
    {
        return $this->openWeekNumber;
    }

    /**
     * @param int $openWeekNumber
     * @return Task
     */
    public function setOpenWeekNumber(int $openWeekNumber): self
    {
        $this->openWeekNumber = $openWeekNumber;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getOpenYearNumber(): ?int
    {
        return $this->openYearNumber;
    }

    /**
     * @param int $openYearNumber
     * @return Task
     */
    public function setOpenYearNumber(int $openYearNumber): self
    {
        $this->openYearNumber = $openYearNumber;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getCloseWeekNumber(): ?int
    {
        return $this->closeWeekNumber;
    }

    /**
     * @param int $closeWeekNumber
     * @return Task
     */
    public function setCloseWeekNumber(int $closeWeekNumber): self
    {
        $this->closeWeekNumber = $closeWeekNumber;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getCloseYearNumber(): ?int
    {
        return $this->closeYearNumber;
    }

    /**
     * @param int $closeYearNumber
     * @return Task
     */
    public function setCloseYearNumber(int $closeYearNumber): self
    {
        $this->closeYearNumber = $closeYearNumber;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getCancelWeekNumber(): ?int
    {
        return $this->cancelWeekNumber;
    }

    /**
     * @param int $cancelWeekNumber
     * @return Task
     */
    public function setCancelWeekNumber(int $cancelWeekNumber): self
    {
        $this->cancelWeekNumber = $cancelWeekNumber;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getCancelYearNumber(): ?int
    {
        return $this->cancelYearNumber;
    }

    /**
     * @param int $cancelYearNumber
     * @return Task
     */
    public function setCancelYearNumber(int $cancelYearNumber): self
    {
        $this->cancelYearNumber = $cancelYearNumber;
        return $this;
    }


    #########################
    ##  OBJECT REL: G & S  ##
    #########################

    /**
     * @return Notebook|null
     */
    public function getNotebook(): ?Notebook
    {
        return $this->notebook;
    }

    /**
     * @param Notebook $notebook
     * @return Task
     */
    public function setNotebook(Notebook $notebook): self
    {
        $this->notebook = $notebook;
        return $this;
    }
}
