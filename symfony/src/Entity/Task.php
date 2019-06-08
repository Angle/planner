<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Angle\Common\Utilities\Random\Random;


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
    private $creationTime;

    /**
     * @ORM\Column(type="datetime")
     */
    private $closeTime;

    /**
     * @ORM\Column(type="datetime")
     */
    private $cancelledTime;


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
    public function setCreationTimeValue()
    {
        $this->creationTime = new DateTime('now');
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
    public function getCreationTime(): ?DateTime
    {
        return $this->creationTime;
    }

    /**
     * @param DateTime $creationTime
     * @return Task
     */
    public function setCreationTime(DateTime $creationTime): self
    {
        $this->creationTime = $creationTime;
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
     * @return DateTime|null
     */
    public function getCancelledTime(): ?DateTime
    {
        return $this->cancelledTime;
    }

    /**
     * @param DateTime $cancelledTime
     * @return Task
     */
    public function setCancelledTime(DateTime $cancelledTime): self
    {
        $this->cancelledTime = $cancelledTime;
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
