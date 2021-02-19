<?php

namespace App\Entity;

use App\Repository\IntervenantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="Intervenant"
 * )
 * @ORM\Entity(repositoryClass=IntervenantRepository::class)
 */
class Intervenant
{
    /**
     * @OA\Property(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"get_intervenant","matiere_get","get_intervenant_by_matiere"})
     */
    private $id;

    /**
     * @OA\Property(ref="#/components/schemas/Personne")
     * @ORM\OneToOne(targetEntity=Personne::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"get_intervenant","matiere_get","get_intervenant_by_matiere"})
     * @var Personne
     */
    private $Personne;

    /**
     * @OA\Property(type="array",@OA\Items(ref="#/components/schemas/Matiere"))
     * @ORM\ManyToMany(targetEntity=Matiere::class, mappedBy="intervenants")
     * @Groups({"get_intervenant"})
     * @var Matiere[]|null
     */
    private $matieres;

    public function __construct()
    {
        $this->matieres = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

/*    public function getEmail(): ?string
    {
        return $this->Personne->getEmail();
    }

    public function setEmail(?string $email): self
    {
        $this->Personne->setEmail($email);

        return $this;
    }*/

    public function getPersonne(): ?Personne
    {
        return $this->Personne;
    }

    public function setPersonne(Personne $Personne): self
    {
        $this->Personne = $Personne;
        $this->Personne->addRole("ROLE_INTER");
        return $this;
    }

    /**
     * @return Collection|Matiere[]
     */
    public function getMatieres(): Collection
    {
        return $this->matieres;
    }

    public function addMatiere(Matiere $matiere): self
    {
        if (!$this->matieres->contains($matiere)) {
            $this->matieres[] = $matiere;
            $matiere->addIntervenant($this);
        }

        return $this;
    }

    public function removeMatiere(Matiere $matiere): self
    {
        if ($this->matieres->removeElement($matiere)) {
            $matiere->removeIntervenant($this);
        }

        return $this;
    }
}
