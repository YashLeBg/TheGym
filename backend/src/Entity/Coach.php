<?php

namespace App\Entity;

use App\Repository\CoachRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CoachRepository::class)]
class Coach extends Utilisateur
{
    #[ORM\Column(type: Types::JSON)]
    #[Groups(['coach:read', 'coach:write'])]
    #[Assert\NotBlank]
    #[Assert\Count(min: 1)]
    private array $specialites = [];

    #[ORM\Column]
    #[Groups(['coach:read', 'coach:write'])]
    #[Assert\NotBlank]
    #[Assert\Type(type: 'float')]
    #[Assert\Positive]
    #[Assert\LessThanOrEqual(100)]
    #[Assert\GreaterThanOrEqual(10)]
    private ?float $tarif_horaire = null;

    /**
     * @var Collection<int, Seance>
     */
    #[ORM\OneToMany(targetEntity: Seance::class, mappedBy: 'coach')]
    #[Groups(['coach:read', 'coach:write'])]
    #[Assert\NotBlank]
    private Collection $seances;

    /**
     * @var Collection<int, FicheDePaie>
     */
    #[ORM\OneToMany(targetEntity: FicheDePaie::class, mappedBy: 'coach')]
    #[Groups(['coach:read', 'coach:write'])]
    #[Assert\NotBlank]
    private Collection $fichesDePaie;

    public function __construct()
    {
        $this->seances = new ArrayCollection();
        $this->fichesDePaie = new ArrayCollection();
    }

    public function getSpecialites(): array
    {
        return $this->specialites;
    }

    public function setSpecialites(array $specialites): static
    {
        $this->specialites = $specialites;

        return $this;
    }

    public function getTarifHoraire(): ?float
    {
        return $this->tarif_horaire;
    }

    public function setTarifHoraire(float $tarif_horaire): static
    {
        $this->tarif_horaire = $tarif_horaire;

        return $this;
    }

    /**
     * @return Collection<int, Seance>
     */
    public function getSeances(): Collection
    {
        return $this->seances;
    }

    public function addSeance(Seance $seance): static
    {
        if (!$this->seances->contains($seance)) {
            $this->seances->add($seance);
            $seance->setCoach($this);
        }

        return $this;
    }

    public function removeSeance(Seance $seance): static
    {
        if ($this->seances->removeElement($seance)) {
            // set the owning side to null (unless already changed)
            if ($seance->getCoach() === $this) {
                $seance->setCoach(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, FicheDePaie>
     */
    public function getFichesDePaie(): Collection
    {
        return $this->fichesDePaie;
    }

    public function addFichesDePaie(FicheDePaie $fichesDePaie): static
    {
        if (!$this->fichesDePaie->contains($fichesDePaie)) {
            $this->fichesDePaie->add($fichesDePaie);
            $fichesDePaie->setCoach($this);
        }

        return $this;
    }

    public function removeFichesDePaie(FicheDePaie $fichesDePaie): static
    {
        if ($this->fichesDePaie->removeElement($fichesDePaie)) {
            // set the owning side to null (unless already changed)
            if ($fichesDePaie->getCoach() === $this) {
                $fichesDePaie->setCoach(null);
            }
        }

        return $this;
    }
}
