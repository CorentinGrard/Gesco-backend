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
     * @OA\Property(type="integer",
     *      readOnly="true")
     * @Groups({"get_promotion",
     *     "get_assistant",
     *     "get_etudiant",
     *     "get_etudiants_by_promotion",
     *     "get_etudiants_for_all_promotions",
     *     "add_semestre_by_promotion",
     *     "get_formation",
     *     "update_promotion",
     *     "delete_promotion",
     *     "get_modules_by_promotion",
     *     "add_promotion"
     * })
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @OA\Property(type="string")
     * @ORM\Column(type="string", length=255)
     * @Groups({
     *     "get_etudiant",
     *     "get_promotion",
     *     "get_etudiants_by_promotion",
     *     "get_formation",
     *     "update_promotion",
     *     "delete_promotion",
     *     "get_modules_by_promotion",
     *     "add_promotion"
     * })
     */
    private $nom;

    /**
     * @OA\Property(type="array",
     *      @OA\Items(
     *          @OA\Property(
     *              property="id",
     *              ref="#/components/schemas/Semestre/properties/id"
     *          ),
     *          @OA\Property(
     *              property="nom",
     *              ref="#/components/schemas/Semestre/properties/nom"
     *          )
     *      ),
     *      readOnly="true"
     * )
     * @ORM\OneToMany(targetEntity=Semestre::class, mappedBy="promotion")
     * @Groups({"get_promotion","get_modules_by_promotion"})
     */
    private $semestres;

    /**
     * @OA\Property(
     *      @OA\Property(
     *          property="id",
     *          ref="#/components/schemas/Formation/properties/id"
     *      ),
     *      @OA\Property(
     *          property="nom",
     *          ref="#/components/schemas/Formation/properties/nom"
     *      ),
     *     @OA\Property(
     *          property="isAlternance",
     *          ref="#/components/schemas/Formation/properties/isAlternance"
     *      ),
     *      readOnly="true"
     * )
     * @ORM\ManyToOne(targetEntity=Formation::class, inversedBy="promotions")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({
     *     "get_promotion",
     *     "get_etudiant",
     *     "update_promotion",
     *     "add_promotion"
     * })
     */
    private $formation;

    /**
     * @OA\Property(
     *      @OA\Property(
     *          property="id",
     *          ref="#/components/schemas/Assistant/properties/id"
     *      ),
     *      readOnly="true"
     * )
     * @ORM\ManyToOne(targetEntity=Assistant::class, inversedBy="promotions")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"get_promotion","update_promotion", "add_promotion"})
     */
    private $assistant;

    /**
     * @ORM\OneToMany(targetEntity=Etudiant::class, mappedBy="Promotion")
     * @Groups({
     *     "get_etudiants_by_promotion",
     *     "get_etudiants_for_all_promotions"
     * })
     */
    private $Etudiants;

    public function __construct()
    {
        $this->semestres = new ArrayCollection();
        $this->Etudiants = new ArrayCollection();
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
     * @OA\Property(property="nomPromotion", type="string",
     *      readOnly="true")
     * @Groups({
     *     "get_promotion",
     *     "get_assistant",
     *     "get_etudiants_by_promotion",
     *     "get_etudiants_for_all_promotions",
     *     "add_semestre_by_promotion",
     *     "get_formation",
     *     "update_promotion",
     *     "add_promotion"
     * })
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

    /**
     * @return Collection|Etudiant[]
     */
    public function getEtudiants(): Collection
    {
        return $this->Etudiants;
    }

    public function addEtudiant(Etudiant $etudiant): self
    {
        if (!$this->Etudiants->contains($etudiant)) {
            $this->Etudiants[] = $etudiant;
            $etudiant->setPromotion($this);
        }

        return $this;
    }

    public function removeEtudiant(Etudiant $etudiant): self
    {
        if ($this->Etudiants->removeElement($etudiant)) {
            // set the owning side to null (unless already changed)
            if ($etudiant->getPromotion() === $this) {
                $etudiant->setPromotion(null);
            }
        }

        return $this;
    }

}
