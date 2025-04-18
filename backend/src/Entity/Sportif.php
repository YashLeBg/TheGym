<?php

namespace App\Entity;

use App\Repository\SportifRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SportifRepository::class)]
class Sportif extends Utilisateur
{
    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date_incription = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['debutant', 'intermediaire', 'avance'])]
    private ?string $niveau_sportif = null;

    #[ORM\ManyToMany(targetEntity: Seance::class, mappedBy: 'sportifs')]
    #[Assert\Count(max: 3)]
    private Collection $seances;

    public function __construct()
    {
        $this->seances = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getNom() . ' ' . $this->getPrenom();
    }


    public function getDateIncription(): ?\DateTimeInterface
    {
        return $this->date_incription;
    }

    public function setDateIncription(\DateTimeInterface $date_incription): static
    {
        $this->date_incription = $date_incription;

        return $this;
    }

    public function getNiveauSportif(): ?string
    {
        return $this->niveau_sportif;
    }

    public function setNiveauSportif(string $niveau_sportif): static
    {
        $this->niveau_sportif = $niveau_sportif;

        return $this;
    }

    public function getSeances(): Collection
    {
        return $this->seances;
    }

    public function addSeance(Seance $seance): static
    {
        if (!$this->seances->contains($seance)) {
            $this->seances->add($seance);
            $seance->addSportif($this);
        }

        return $this;
    }

    public function removeSeance(Seance $seance): static
    {
        if ($this->seances->removeElement($seance)) {
            $seance->removeSportif($this);
        }

        return $this;
    }
}
