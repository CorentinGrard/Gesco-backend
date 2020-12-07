<?php

namespace App\Entity;

use App\Repository\PromotionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PromotionRepository::class)
 */
class Promotion
{
    /**
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
     * @ORM\OneToMany(targetEntity=Semestre::class, mappedBy="promotion")
     */
    private $semestres;

    /**
     * @ORM\ManyToOne(targetEntity=Formation::class, inversedBy="promotions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $formation;

    /**
     * @ORM\ManyToOne(targetEntity=Assistant::class, inversedBy="promotions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $assistant;

    public function __construct()
    {
        $this->semestres = new ArrayCollection();
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
     * @return Collection|Semestre[]
     */
    public function getSemestres(): Collection
    {
        return $this->semestres;
    }

    public function addSemestre(Semestre $semestre): self
    {
        if (!$this->semestres->contains($semestre)) {
            $this->semestres[] = $semestre;
            $semestre->setPromotion($this);
        }

        return $this;
    }

    public function removeSemestre(Semestre $semestre): self
    {
        if ($this->semestres->removeElement($semestre)) {
            // set the owning side to null (unless already changed)
            if ($semestre->getPromotion() === $this) {
                $semestre->setPromotion(null);
            }
        }

        return $this;
    }

    public function getFormation(): ?Formation
    {
        return $this->formation;
    }

    public function setFormation(?Formation $formation): self
    {
        $this->formation = $formation;

        return $this;
    }

    public function getModules(){
        $semestres = $this->semestres;

        $modules = [];
        foreach($semestres as $semestre){
            $mods = $semestre->getModules();
            foreach($mods as $module){
                array_push($modules, $module);
            }
        }
        return $modules;
    }

    public function getMatieres(){
        $modules = $this->getModules();

        $matieres = [];
        foreach($modules as $module){
            $mats = $module->getMatieres();
            foreach($mats as $matiere){
                array_push($matieres, $matiere);
            }
        }
        return $matieres;
    }

    public function getSessions()
    {
        $matieres = $this->getMatieres();

        $sessions = [];
        foreach($matieres as $matiere){
            $sesss = $matiere->getSessions();
            foreach($sesss as $session){
                array_push($sessions, $session);
            }
        }
        return $sessions;
    }

    public function getAssistant(): ?Assistant
    {
        return $this->assistant;
    }

    public function setAssistant(?Assistant $assistant): self
    {
        $this->assistant = $assistant;

        return $this;
    }

    public function getArray(){
        return [
            "id" => $this->getId(),
            "nom" => $this->getNom(),
            "idFormation" => $this->getFormation()->getId(),
            "nomFormation" => $this->getFormation()->getNom()
        ];
    }

}
