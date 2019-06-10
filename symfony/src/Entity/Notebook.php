<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Angle\Common\Utilities\Random\Random;


/**
 * @ORM\Entity(repositoryClass="App\Repository\NotebookRepository")
 * @ORM\Table(name="Notebooks")
 */
class Notebook
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
    private $notebookId;

    /**
     * @ORM\Column(type="string", unique=true)
     */
    private $code;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private $name;


    #########################
    ## OBJECT RELATIONSHIP ##
    #########################

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="notebooks")
     * @ORM\JoinColumn(referencedColumnName="user_id", nullable=false)
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Task", mappedBy="notebook", orphanRemoval=true)
     */
    private $tasks;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ShareMap", mappedBy="notebook", orphanRemoval=true)
     */
    private $shareMaps;


    #########################
    ##     CONSTRUCTOR     ##
    #########################

    public function __construct()
    {
        $this->code = Random::generateString(self::CODE_LENGTH);

        $this->tasks = new ArrayCollection();
        $this->shareMaps = new ArrayCollection();
    }


    #########################
    ##   SPECIAL METHODS   ##
    #########################

    public function hasPermission(User $user): bool
    {
        if ($this->getUser()->getUserId() == $user->getUserId()) {
            return true;
        }

        foreach ($this->getShareMaps() as $map) {
            if ($map->getUser()->getUserId() == $user->getUserId()) {
                return true;
            }
        }

        return false;
    }

    public function isShared(): bool
    {
        return (count($this->getShareMaps()) > 0);
    }


    #########################
    ## GETTERS AND SETTERS ##
    #########################

    /**
     * @return int|null
     */
    public function getNotebookId(): ?int
    {
        return $this->notebookId;
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
     * @return Notebook
     */
    public function setCode(string $code): self
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Notebook
     */
    public function setName(string $name): self
    {
        $this->name = $name;
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
     * @param User $user
     * @return Notebook
     */
    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return Collection|Task[]
     */
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function addTask(Task $task): self
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks[] = $task;
            $task->setNotebook($this);
        }

        return $this;
    }

    public function removeTask(Task $task): self
    {
        if ($this->tasks->contains($task)) {
            $this->tasks->removeElement($task);
            // set the owning side to null (unless already changed)
            if ($task->getNotebook() === $this) {
                $task->setNotebook(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ShareMap[]
     */
    public function getShareMaps(): Collection
    {
        return $this->shareMaps;
    }

    public function addShareMap(ShareMap $shareMap): self
    {
        if (!$this->shareMaps->contains($shareMap)) {
            $this->shareMaps[] = $shareMap;
            $shareMap->setNotebook($this);
        }

        return $this;
    }

    public function removeShareMap(ShareMap $shareMap): self
    {
        if ($this->shareMaps->contains($shareMap)) {
            $this->shareMaps->removeElement($shareMap);
            // set the owning side to null (unless already changed)
            if ($shareMap->getNotebook() === $this) {
                $shareMap->setNotebook(null);
            }
        }

        return $this;
    }
}
