<?php

namespace App\Entity;

use App\Repository\ResponsableRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema()
 * @ORM\Entity(repositoryClass=ResponsableRepository::class)
 */
class Responsable
{
    /**
     * @OA\Property(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({
     *     "get_responsable",
     *     "get_formation"
     * })
     */
    private $id;

    /**
     * @OA\Property(ref="#/components/schemas/Personne")
     * @ORM\OneToOne(targetEntity=Personne::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"get_formation"})
     * @var Personne
     */
    private $Personne;

    /**
     * @OA\Property(type="array",
     *     @OA\Items(ref="#/components/schemas/Formation")
     * )
     * @ORM\OneToMany(targetEntity=Formation::class, mappedBy="responsable")
     * @var Formation[]
     */
    private $formations;

    public function __construct()
    {
        $this->formations = new ArrayCollection();
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

    /**
     * @return Collection|Formation[]
     */
    public function getFormations(): Collection
    {
        return $this->formations;
    }

    public function addFormation(Formation $formation): self
    {
        if (!$this->formations->contains($formation)) {
            $this->formations[] = $formation;
            $formation->setRespo($this);
        }

        return $this;
    }

    public function removeFormation(Formation $formation): self
    {
        if ($this->formations->removeElement($formation)) {
            // set the owning side to null (unless already changed)
            if ($formation->getRespo() === $this) {
                $formation->setRespo(null);
            }
        }

        return $this;
    }
}
