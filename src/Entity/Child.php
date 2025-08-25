<?php

namespace App\Entity;

use App\Repository\ChildRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChildRepository::class)]
class Child
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $firstname = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column]
    private ?int $points = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $gender = null;

    /**
     * @var Collection<int, Historic>
     */
    #[ORM\OneToMany(targetEntity: Historic::class, mappedBy: 'child', orphanRemoval: true)]
    private Collection $historics;

    public function __construct()
    {
        $this->historics = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPoints(): ?int
    {
        return $this->points;
    }

    public function setPoints(int $points): static
    {
        $this->points = $points;

        return $this;
    }

    public function getGender(): ?int
    {
        return $this->gender;
    }

    public function setGender(int $gender): static
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * @return Collection<int, Historic>
     */
    public function getHistorics(): Collection
    {
        return $this->historics;
    }

    public function addHistoric(Historic $historic): static
    {
        if (!$this->historics->contains($historic)) {
            $this->historics->add($historic);
            $historic->setChild($this);
        }

        return $this;
    }

    public function removeHistoric(Historic $historic): static
    {
        if ($this->historics->removeElement($historic)) {
            // set the owning side to null (unless already changed)
            if ($historic->getChild() === $this) {
                $historic->setChild(null);
            }
        }

        return $this;
    }

    public function __toString(): string 
    {
        return $this->getFirstname();
    }

    public function getEmailCrypted(): string
    {
        return hash('sha256', $this->email);
    }
}
