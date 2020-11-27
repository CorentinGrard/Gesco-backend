<?php

namespace App\Entity;

use App\Repository\EtudiantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EtudiantRepository::class)
 */
class Etudiant
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=Personne::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $Personne;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isAlternant;

    /**
     * @ORM\OneToMany(targetEntity=Note::class, mappedBy="Etudiant")
     */
    private $Notes;

    public function __construct()
    {
        $this->Notes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPersonne(): ?Personne
    {
        return $this->Personne;
    }

    public function setPersonne(Personne $Personne): self
    {
        $this->Personne = $Personne;

        return $this;
    }

    public function getIsAlternant(): ?bool
    {
        return $this->isAlternant;
    }

    public function setIsAlternant(bool $isAlternant): self
    {
        $this->isAlternant = $isAlternant;

        return $this;
    }

    /**
     * @return Collection|Note[]
     */
    public function getNotes(): Collection
    {
        return $this->Notes;
    }

    public function addNote(Note $note): self
    {
        if (!$this->Notes->contains($note)) {
            $this->Notes[] = $note;
            $note->setEtudiant($this);
        }

        return $this;
    }

    public function removeNote(Note $note): self
    {
        if ($this->Notes->removeElement($note)) {
            // set the owning side to null (unless already changed)
            if ($note->getEtudiant() === $this) {
                $note->setEtudiant(null);
            }
        }

        return $this;
    }
}
