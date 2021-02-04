<?php

namespace App\Entity;

use App\Repository\PromotionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Annotations as OA;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @OA\Schema()
 * @ORM\Entity(repositoryClass=PromotionRepository::class)
 */
class Promotion
{
    /**
     * @OA\Property(type="integer")
     * @Groups({"get_all_promotions", "get_promotion", "promos_assistant"})
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @OA\Property(type="string")
     * @ORM\Column(type="string", length=255)
     * @Groups({"get_promotion"})
     */
    private $nom;

    /**
     * @OA\Property(
     *      property="semestres",
     *      type="array",
     *      @OA\Items(
     *          @OA\Property(
     *              property="id",
     *              type="integer"
     *          )
     *      )
     * )
     * @ORM\OneToMany(targetEntity=Semestre::class, mappedBy="promotion")
     * @Groups({"get_promotion"})
     */
    private $semestres;

    /**
     * @OA\Property(
     *      property="formation",
     *      @OA\Property(
     *          property="id",
     *          type="integer"
     *      )
     * )
     * @ORM\ManyToOne(targetEntity=Formation::class, inversedBy="promotions")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"get_promotion", "get_all_promotions"})
     */
    private $formation;

    /**
     * @OA\Property(
     *     property="assistant",
     *     @OA\Property(property="id", type="integer")
     * )
     * @ORM\ManyToOne(targetEntity=Assistant::class, inversedBy="promotions")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"get_promotion"})
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

    /**
     * @OA\Property(property="nomPromotion", type="string")
     * @Groups({"get_all_promotions", "promos_assistant"})
     */
    public function getNomPromotion(): string
    {
        return $this->getFormation()->getNom() . " " . $this->getNom();
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
