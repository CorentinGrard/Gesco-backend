<?php

namespace App\Entity;

use App\Repository\SemestreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Annotations as OA;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @OA\Schema()
 * @ORM\Entity(repositoryClass=SemestreRepository::class)
 */
class Semestre
{
    /**
     * @OA\Property(type="integer")
     * @Groups({"semestre_get", "module_get"})
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    /**
     * @ORM\OneToMany(targetEntity=Module::class, mappedBy="semestre")
     */
    private $modules;

    /**
     * @ORM\ManyToOne(targetEntity=Promotion::class, inversedBy="semestres")
     * @ORM\JoinColumn(nullable=false)
     */
    private $promotion;

    public function __construct()
    {
        $this->modules = new ArrayCollection();
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

    /**
     * @return Collection|Module[]
     */
    public function getModules(): Collection
    {
        return $this->modules;
    }

    public function addModule(Module $module): self
    {
        if (!$this->modules->contains($module)) {
            $this->modules[] = $module;
            $module->setSemestre($this);
        }

        return $this;
    }

    public function removeModule(Module $module): self
    {
        if ($this->modules->removeElement($module)) {
            // set the owning side to null (unless already changed)
            if ($module->getSemestre() === $this) {
                $module->setSemestre(null);
            }
        }

        return $this;
    }

    public function getPromotion(): ?Promotion
    {
        return $this->promotion;
    }

    public function setPromotion(?Promotion $promotion): self
    {
        $this->promotion = $promotion;

        return $this;
    }

    public function getArray(){
        $modules = $this->getModules();
        $moduleArray = [];
        foreach ($modules as $module) {
            array_push($moduleArray, $module->getId());
        }

        return [
            "id"=>$this->getId(),
            "nom"=>$this->getNom(),
            "idPromotion"=>$this->getPromotion()->getId(),
            "nomPromotion"=>$this->getPromotion()->getNom(),
            "nomFormation"=>$this->getPromotion()->getFormation()->getNom(),
            "idModules"=>$moduleArray
        ];
    }

}
