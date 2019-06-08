<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use DateTime;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(name="Users")
 */
class User implements UserInterface
{
    #########################
    ##        PRESETS      ##
    #########################

    const GENERIC_PASSWORD_LENGTH = 9;

    #########################
    ##      PROPERTIES     ##
    #########################

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $userId;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $lastName;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastLoginTime;


    #########################
    ## OBJECT RELATIONSHIP ##
    #########################

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Notebook", mappedBy="user", orphanRemoval=true)
     */
    private $notebooks;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ShareMap", mappedBy="user", orphanRemoval=true)
     */
    private $shareMaps;


    #########################
    ##     CONSTRUCTOR     ##
    #########################

    public function __construct()
    {
        $this->notebooks = new ArrayCollection();
        $this->shareMaps = new ArrayCollection();
    }


    #########################
    ##   SPECIAL METHODS   ##
    #########################

    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    #########################
    ##  INTERFACE METHODS  ##
    #########################

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    #########################
    ## GETTERS AND SETTERS ##
    #########################

    /**
     * @return int|null
     */
    public function getUserId(): ?int
    {
        return $this->userId;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return User
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @param string $password
     * @return User
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @param array $roles
     * @return User
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     * @return User
     */
    public function setFirstName($firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     * @return User
     */
    public function setLastName($lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getLastLoginTime(): ?DateTime
    {
        return $this->lastLoginTime;
    }

    /**
     * @param DateTime|null $lastLoginTime
     * @return User
     */
    public function setLastLoginTime(?DateTime $lastLoginTime): self
    {
        $this->lastLoginTime = $lastLoginTime;
        return $this;
    }


    #########################
    ##  OBJECT REL: G & S  ##
    #########################

    /**
     * @return Collection|Notebook[]
     */
    public function getNotebooks(): Collection
    {
        return $this->notebooks;
    }

    public function addNotebook(Notebook $notebook): self
    {
        if (!$this->notebooks->contains($notebook)) {
            $this->notebooks[] = $notebook;
            $notebook->setUser($this);
        }

        return $this;
    }

    public function removeNotebook(Notebook $notebook): self
    {
        if ($this->notebooks->contains($notebook)) {
            $this->notebooks->removeElement($notebook);
            // set the owning side to null (unless already changed)
            if ($notebook->getUser() === $this) {
                $notebook->setUser(null);
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
            $shareMap->setUser($this);
        }

        return $this;
    }

    public function removeShareMap(ShareMap $shareMap): self
    {
        if ($this->shareMaps->contains($shareMap)) {
            $this->shareMaps->removeElement($shareMap);
            // set the owning side to null (unless already changed)
            if ($shareMap->getUser() === $this) {
                $shareMap->setUser(null);
            }
        }

        return $this;
    }

}
