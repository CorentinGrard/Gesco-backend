<?php

namespace App\Entity;

use App\Repository\EtudiantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Annotations as OA;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @OA\Schema()
 * @ORM\Entity(repositoryClass=EtudiantRepository::class)
 */
class Etudiant
{
    /**
     * @OA\Property(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"get_etudiant"})
     */
    private $id;

    /**
     * @OA\Property(
     *      property="Personne",
     *      allOf={@OA\Property(ref="#/components/schemas/Personne")}
     * )
     * @ORM\OneToOne(targetEntity=Personne::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"get_etudiant","get_etudiants_by_promotion","get_etudiants_for_all_promotions"})
     * @var Personne
     */
    private $Personne;

    /**
     * @OA\Property(type="boolean")
     * @ORM\Column(type="boolean")
     * @Groups({"get_etudiant"})
     */
    private $isAlternant;

    /**
     * @OA\Property(type="array",
     *      @OA\Items(
     *          @OA\Property(
     *              property="id",
     *              ref="#/components/schemas/Note/properties/id"
     *          ),
     *          @OA\Property(
     *              property="note",
     *              ref="#/components/schemas/Note/properties/note"
     *          )
     *      )
     * )
     * @ORM\OneToMany(targetEntity=Note::class, mappedBy="Etudiant")
     * @Groups({"get_etudiant"})
     * @var Note[] | ArrayCollection
     */
    private $Notes;

    /**
     * @ORM\ManyToOne(targetEntity=Promotion::class, inversedBy="Etudiants")
     */
    private $Promotion;

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

    public function getPromotion(): ?Promotion
    {
        return $this->Promotion;
    }

    public function setPromotion(?Promotion $Promotion): self
    {
        $this->Promotion = $Promotion;

        return $this;
    }
}
