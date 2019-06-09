<?php

namespace App\Entity;

use DateTime;

use Doctrine\ORM\Mapping as ORM;

use Angle\Common\Utilities\Random\Random;

use App\Utility\Week;


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

    const CODE_LENGTH = 32;

    const STATUS_OPEN = 1;
    const STATUS_CLOSED = 2;
    const STATUS_CANCELLED = 3;


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
     * @ORM\Column(type="text")
     */
    private $details;

    /**
     * @ORM\Column(type="datetime")
     */
    private $openTimestamp;

    /**
     * @ORM\Column(type="smallint")
     */
    private $openWeekNumber;

    /**
     * @ORM\Column(type="smallint")
     */
    private $openYearNumber;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $closeTimestamp;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $closeWeekNumber;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $closeYearNumber;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $cancelTimestamp;

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

        $this->openTimestamp = new DateTime('now');

        $week = Week::newFromDateTime($this->openTimestamp);
        $this->openWeekNumber = $week->getWeek();
        $this->openYearNumber = $week->getYear();
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
        if (!is_null($this->cancelTimestamp)) {
            return self::STATUS_CANCELLED;
        } elseif (!is_null($this->closeTimestamp)) {
            return self::STATUS_CLOSED;
        } else {
            return self::STATUS_OPEN;
        }
    }

    public function isOlderThanWeek(Week $comparisonWeek): bool
    {
        // Check if the Task's Open Week is older than the comparison week
        return $this->getOpenWeek()->isOlderThanWeek($comparisonWeek);
    }

    public function showPulledFlag(Week $queryWeek, Week $currentWeek): bool
    {

    }

    public function showKickedFlag(Week $queryWeek, Week $currentWeek): bool
    {

    }

    public function wasKickedOnWeek(Week $queryWeek): bool
    {
        // if this is not our current week AND the task was not closed or deleted on this week, mark it wish a > #}

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
     * @return DateTime|null
     */
    public function getOpenTimestamp(): ?DateTime
    {
        return $this->openTimestamp;
    }

    /**
     * @param DateTime $openTimestamp
     * @return Task
     */
    public function setOpenTimestamp(DateTime $openTimestamp): self
    {
        $this->openTimestamp = $openTimestamp;
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
     * @return DateTime|null
     */
    public function getCloseTimestamp(): ?DateTime
    {
        return $this->closeTimestamp;
    }

    /**
     * @param DateTime $closeTimestamp
     * @return Task
     */
    public function setCloseTimestamp(DateTime $closeTimestamp): self
    {
        $this->closeTimestamp = $closeTimestamp;
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
     * @return DateTime|null
     */
    public function getCancelTimestamp(): ?DateTime
    {
        return $this->cancelTimestamp;
    }

    /**
     * @param DateTime $cancelTimestamp
     * @return Task
     */
    public function setCancelTimestamp(DateTime $cancelTimestamp): self
    {
        $this->cancelTimestamp = $cancelTimestamp;
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
