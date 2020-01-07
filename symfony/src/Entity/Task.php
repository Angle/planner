<?php

namespace App\Entity;

use DateTime;
use DateTimeZone;

use Doctrine\Common\Collections\ArrayCollection;
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

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $focusDate;


    #########################
    ## OBJECT RELATIONSHIP ##
    #########################

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Notebook", inversedBy="tasks")
     * @ORM\JoinColumn(referencedColumnName="notebook_id", nullable=false)
     */
    private $notebook;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TaskLog", mappedBy="task", orphanRemoval=true)
     */
    private $taskLogs;


    #########################
    ##     CONSTRUCTOR     ##
    #########################

    public function __construct()
    {
        $this->code = Random::generateString(self::CODE_LENGTH);

        $tz = new DateTimeZone(self::DEFAULT_TIMEZONE);
        $this->calculateAndSetOpenWeekFromTimestamp(new DateTime('now', $tz));

        $this->taskLogs = new ArrayCollection();
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
        if ($this->getStatus() == self::STATUS_CLOSED) {
            // If the task is now CLOSED there are only two valid bullets:
            // a. if the query week is older than the actual close date, display an arrow
            // b. if the query week is the same as the close date, display times
            // c. unknown, the task should not be returned by the repository in the first place
            if ($queryWeek->isOlderThanWeek($this->getCloseWeek())) {
                return self::BULLET_ARROW;
            } elseif ($queryWeek->equals($this->getCloseWeek())) {
                return self::BULLET_TIMES;
            } else {
                return self::BULLET_UNKNOWN;
            }
        } elseif ($this->getStatus() == self::STATUS_CANCELLED) {
            // If the task is now CANCELLED there are only two valid bullets:
            // a. if the query week is older than the actual close date, display an arrow
            // b. if the query week is the same as the close date, display a dash (and also strike it)
            // c. unknown, the task should not be returned by the repository in the first place
            if ($queryWeek->isOlderThanWeek($this->getCancelWeek())) {
                return self::BULLET_ARROW;
            } elseif ($queryWeek->equals($this->getCancelWeek())) {
                return self::BULLET_DASH;
            } else {
                return self::BULLET_UNKNOWN;
            }
        } elseif ($this->getStatus() == self::STATUS_OPEN) {
            // If the tag is still OPEN
            // a. the query week is the same as the current week, or the query week is newer than the current week,
            //    we must show a dot
            // b. the query week is older than the current week, therefore the task has already been "kicked" and we
            //    must show an arrow
            if ($queryWeek->equals($currentWeek) || $queryWeek->isNewerThanWeek($currentWeek)) {
                return self::BULLET_DOT;
            } else {
                return self::BULLET_ARROW;
            }
        }

        // Any other case or status shouldn't happen
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


    public function calculateAndSetOpenWeekFromTimestamp(\DateTime $time)
    {
        $week = Week::newFromDateTime($time);
        $this->openWeekNumber = $week->getWeek();
        $this->openYearNumber = $week->getYear();
    }

    public function getFocusToday()
    {
        if (!$this->focusDate) {
            return false;
        }

        if ($this->getStatus() != self::STATUS_OPEN) {
            return false;
        }

        $tz = new DateTimeZone(self::DEFAULT_TIMEZONE);
        $today = new DateTime('now', $tz);
        $today->setTime(0, 0, 0);

        $focus = $this->getFocusDate();
        $focus->setTimezone($tz);

        if ($focus->format('Y-m-d') === $today->format('Y-m-d')) {
            return true;
        }

        return false;
    }

    public function getFocusOverdue()
    {
        if (!$this->focusDate) {
            return false;
        }

        $tz = new DateTimeZone(self::DEFAULT_TIMEZONE);
        $today = new DateTime('now', $tz);
        $today->setTime(0, 0, 0);

        $focus = $this->getFocusDate();
        $focus->setTimezone($tz);
        $focus->setTime(0,0,0);

        if ($this->getStatus() == self::STATUS_OPEN && $focus < $today) {
            return true;
        }

        return false;
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
     * @param int|null $closeWeekNumber
     * @return Task
     */
    public function setCloseWeekNumber(int $closeWeekNumber = null): self
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
     * @param int|null $closeYearNumber
     * @return Task
     */
    public function setCloseYearNumber(int $closeYearNumber = null): self
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
     * @param int|null $cancelWeekNumber
     * @return Task
     */
    public function setCancelWeekNumber(int $cancelWeekNumber = null): self
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
     * @param int|null $cancelYearNumber
     * @return Task
     */
    public function setCancelYearNumber(int $cancelYearNumber = null): self
    {
        $this->cancelYearNumber = $cancelYearNumber;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getFocusDate(): ?DateTime
    {
        return $this->focusDate;
    }

    /**
     * @param DateTime|null $focusDate
     * @return Task
     */
    public function setFocusDate(?DateTime $focusDate): self
    {
        $this->focusDate = $focusDate;
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

    /**
     * @return ArrayCollection|TaskLog[]
     */
    public function getTaskLogs(): ArrayCollection
    {
        return $this->taskLogs;
    }

    public function addTaskLog(TaskLog $taskLog): self
    {
        if (!$this->taskLogs->contains($taskLog)) {
            $this->taskLogs[] = $taskLog;
            $taskLog->setTask($this);
        }

        return $this;
    }

    public function removeTaskLog(TaskLog $taskLog): self
    {
        if ($this->taskLogs->contains($taskLog)) {
            $this->taskLogs->removeElement($taskLog);
            // set the owning side to null (unless already changed)
            if ($taskLog->getTask() === $this) {
                $taskLog->setTask(null);
            }
        }

        return $this;
    }
}
