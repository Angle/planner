<?php

namespace App\Entity;

use Angle\Common\Utilities\Random\Random;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ShareMapRepository")
 * @ORM\Table(name="ShareMaps")
 */
class ShareMap
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
    private $shareMapId;

    /**
     * @ORM\Column(type="string", unique=true)
     */
    private $code;


    #########################
    ## OBJECT RELATIONSHIP ##
    #########################

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="shareMaps")
     * @ORM\JoinColumn(referencedColumnName="user_id", nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Notebook", inversedBy="shareMaps")
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

    // nothing for now...


    #########################
    ## GETTERS AND SETTERS ##
    #########################

    /**
     * @return int|null
     */
    public function getShareMapId(): ?int
    {
        return $this->shareMapId;
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
     * @return ShareMap
     */
    public function setCode(string $code): self
    {
        $this->code = $code;
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
     * @return ShareMap
     */
    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return Notebook|null
     */
    public function getNotebook(): ?Notebook
    {
        return $this->notebook;
    }

    /**
     * @param Notebook $notebook
     * @return ShareMap
     */
    public function setNotebook(Notebook $notebook): self
    {
        $this->notebook = $notebook;
        return $this;
    }
}
