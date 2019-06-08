<?php

namespace App\Entity;

use DateTime;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
    private $openTime;

    /**
     * @ORM\Column(type="smallint")
     */
    private $openWeek;

    /**
     * @ORM\Column(type="smallint")
     */
    private $openYear;

    /**
     * @ORM\Column(type="datetime")
     */
    private $closeTime;

    /**
     * @ORM\Column(type="smallint")
     */
    private $closeWeek;

    /**
     * @ORM\Column(type="smallint")
     */
    private $closeYear;

    /**
     * @ORM\Column(type="datetime")
     */
    private $cancelTimestamp;

    /**
     * @ORM\Column(type="smallint")
     */
    private $cancelWeekNumber;

    /**
     * @ORM\Column(type="smallint")
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
    }


    #########################
    ##   SPECIAL METHODS   ##
    #########################

    /**
     * @ORM\PrePersist
     */
    public function setOpenTimeValue()
    {
        $this->openTime = new DateTime('now');
    }

    public function getOpenWeekCode()
    {
        return $this->openYear.'-'.WeekCode::strpad($this->openWeek, 2, '0');
    }

    public function getCloseWeekCode()
    {
        return $this->closeYear.'-'.WeekCode::strpad($this->closeWeek, 2, '0');
    }

    public function getCancelWeek(): ?Week
    {
        if ($this->cancelYearNumber && $this->cancelWeekNumber) {
            return new Week($this->cancelYearNumber, $this->cancelWeekNumber);
        } else {
            return null;
        }
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
    public function getOpenTime(): ?DateTime
    {
        return $this->openTime;
    }

    /**
     * @param DateTime $openTime
     * @return Task
     */
    public function setOpenTime(DateTime $openTime): self
    {
        $this->openTime = $openTime;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getOpenWeek(): ?int
    {
        return $this->openWeek;
    }

    /**
     * @param int $openWeek
     * @return Task
     */
    public function setOpenWeek(int $openWeek): self
    {
        $this->openWeek = $openWeek;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getOpenYear(): ?int
    {
        return $this->openYear;
    }

    /**
     * @param int $openYear
     * @return Task
     */
    public function setOpenYear(int $openYear): self
    {
        $this->openYear = $openYear;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getCloseTime(): ?DateTime
    {
        return $this->closeTime;
    }

    /**
     * @param DateTime $closeTime
     * @return Task
     */
    public function setCloseTime(DateTime $closeTime): self
    {
        $this->closeTime = $closeTime;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getCloseWeek(): ?int
    {
        return $this->closeWeek;
    }

    /**
     * @param int $closeWeek
     * @return Task
     */
    public function setCloseWeek(int $closeWeek): self
    {
        $this->closeWeek = $closeWeek;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getCloseYear(): ?int
    {
        return $this->closeYear;
    }

    /**
     * @param int $closeYear
     * @return Task
     */
    public function setCloseYear(int $closeYear): self
    {
        $this->closeYear = $closeYear;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getCancelTime(): ?DateTime
    {
        return $this->cancelTime;
    }

    /**
     * @param DateTime $cancelTime
     * @return Task
     */
    public function setCancelTime(DateTime $cancelTime): self
    {
        $this->cancelTime = $cancelTime;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getCancelWeek(): ?int
    {
        return $this->cancelWeek;
    }

    /**
     * @param int $cancelWeek
     * @return Task
     */
    public function setCancelWeek(int $cancelWeek): self
    {
        $this->cancelWeek = $cancelWeek;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getCancelYear(): ?int
    {
        return $this->cancelYear;
    }

    /**
     * @param int $cancelYear
     * @return Task
     */
    public function setCancelYear(int $cancelYear): self
    {
        $this->cancelYear = $cancelYear;
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
