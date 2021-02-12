<?php

namespace App\Entity;

use App\Repository\ModuleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Annotations as OA;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @OA\Schema()
 * @ORM\Entity(repositoryClass=ModuleRepository::class)
 */
class Module
{
    /**
     * @OA\Property(type="integer", readOnly="true")
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"module_get", "matiere_get", "semestre_get", "get_notes_etudiant"})
     */
    private $id;

    /**
     * @OA\Property(type="string"))
     * @ORM\Column(type="string", length=255)
     * @Groups({"module_get", "matiere_get", "semestre_get", "get_notes_etudiant"})
     */
    private $nom;

    /**
     * @OA\Property(type="integer"))
     * @ORM\Column(type="smallint")
     * @Groups({"module_get", "get_notes_etudiant"})
     */
    private $ects;

    /**
     * @OA\Property(
     *      @OA\Items(
     *          @OA\Property(
     *              property="id",
     *              ref="#/components/schemas/Matiere/properties/id"
     *          ),
     *          @OA\Property(
     *              property="nom",
     *              ref="#/components/schemas/Matiere/properties/nom"
     *          )
     *      ),
     *      readOnly="true"
     * )
     * @ORM\OneToMany(targetEntity=Matiere::class, mappedBy="module")
     * @Groups({"module_get"})
     */
    private $matieres;

    /**
     * @OA\Property(
     *      @OA\Property(
     *          property="id",
     *          ref="#/components/schemas/Semestre/properties/id"
     *      ),
     *      @OA\Property(
     *          property="nom",
     *          ref="#/components/schemas/Semestre/properties/nom"
     *      ),
     *      readOnly="true"
     * )
     * @ORM\ManyToOne(targetEntity=Semestre::class, inversedBy="modules")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"module_get", "get_notes_etudiant"})
     */
    private $semestre;

    public function __construct()
    {
        $this->matieres = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getEcts(): ?int
    {
        return $this->ects;
    }

    public function setEcts(int $ects): self
    {
        $this->ects = $ects;

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
            $matiere->setModule($this);
        }

        return $this;
    }

    public function removeMatiere(Matiere $matiere): self
    {
        if ($this->matieres->removeElement($matiere)) {
            // set the owning side to null (unless already changed)
            if ($matiere->getModule() === $this) {
                $matiere->setModule(null);
            }
        }

        return $this;
    }

    public function getSemestre(): ?Semestre
    {
        return $this->semestre;
    }

    public function setSemestre(?Semestre $semestre): self
    {
        $this->semestre = $semestre;

        return $this;
    }

    public function getArray()
    {
        $matieres = [];
        foreach($this->getMatieres() as $matiere){
            array_push($matieres, $matiere->getId());
        }
        return [
            "id" => $this->getId(),
            "nom" => $this->getNom(),
            "matieres" => $matieres,
            "ects" => $this->getEcts(),
            "idSemestre" => $this->getSemestre()->getId(),
            "nomSemestre" => $this->getSemestre()->getNom()
        ];
    }
}
