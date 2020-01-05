<?php

namespace App\Entity;

use Angle\Common\Utilities\Random\Random;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;

use DateTime;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TaskLogRepository")
 * @ORM\Table(name="TaskLogs")
 * @ORM\HasLifecycleCallbacks()
 */
class TaskLog
{
    #########################
    ##        PRESETS      ##
    #########################

    const CODE_LENGTH = 16;


    #########################
    ##      PROPERTIES     ##
    #########################

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $taskLogId;

    /**
     * @ORM\Column(type="string", unique=true)
     */
    private $code;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $timestamp;


    #########################
    ## OBJECT RELATIONSHIP ##
    #########################

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="taskLogs")
     * @ORM\JoinColumn(referencedColumnName="user_id", nullable=true)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Task", inversedBy="taskLogs")
     * @ORM\JoinColumn(referencedColumnName="task_id", nullable=false)
     */
    private $task;


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
    public function setTimestampValue()
    {
        $this->timestamp = new DateTime('now');
    }


    #########################
    ## GETTERS AND SETTERS ##
    #########################

    /**
     * @return int|null
     */
    public function getTaskLogId(): ?int
    {
        return $this->taskLogId;
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
     * @return TaskLog
     */
    public function setCode(string $code): self
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getTimestamp(): ?DateTime
    {
        return $this->timestamp;
    }

    /**
     * @param DateTime $timestamp
     * @return TaskLog
     */
    public function setTimestamp(DateTime $timestamp): self
    {
        $this->timestamp = $timestamp;
        return $this;
    }



    #########################
    ##  OBJECT REL: G & S  ##
    #########################

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User|null $user
     * @return TaskLog
     */
    public function setUser(User $user = null): self
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return Task|null
     */
    public function getTask(): ?Task
    {
        return $this->task;
    }

    /**
     * @param Task $task
     * @return TaskLog
     */
    public function setTask(Task $task): self
    {
        $this->task = $task;
        return $this;
    }
}
