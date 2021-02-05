<?php

namespace App\Entity;

use App\Repository\NoteRepository;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Annotations as OA;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @OA\Schema()
 * @ORM\Entity(repositoryClass=NoteRepository::class)
 */
class Note
{
    /**
     * @OA\Property(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"notes_etudiant"})
     */
    private $id;

    /**
     * @OA\Property(property="note",type="number", format="float")
     * @ORM\Column(type="float", scale=2)
     * @Groups({"notes_etudiant"})
     */
    private $note;

    /**
     * @OA\Property(property="etudiant", ref="#/components/schemas/Etudiant"),
     * @ORM\ManyToOne(targetEntity=Etudiant::class, inversedBy="Notes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $Etudiant;

    /**
     * @OA\Property(property="matiere", @OA\Property(property = "id",type="integer"))
     * @ORM\ManyToOne(targetEntity=Matiere::class, inversedBy="notes")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"notes_etudiant"})
     */
    private $Matiere;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNote(): ?float
    {
        return $this->note;
    }

    public function setNote(float $note): self
    {
        $this->note = $note;

        return $this;
    }

    public function getEtudiant(): ?Etudiant
    {
        return $this->Etudiant;
    }

    public function setEtudiant(?Etudiant $Etudiant): self
    {
        $this->Etudiant = $Etudiant;

        return $this;
    }

    public function getMatiere(): ?Matiere
    {
        return $this->Matiere;
    }

    public function setMatiere(?Matiere $Matiere): self
    {
        $this->Matiere = $Matiere;

        return $this;
    }
}
