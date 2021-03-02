<?php

namespace App\Entity;

use App\Repository\FormationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Annotations as OA;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * @OA\Schema()
 * @ORM\Entity(repositoryClass=FormationRepository::class)
 */
class Formation
{
    /**
     * @OA\Property(type="integer")
     * @Groups({
     *     "get_etudiant",
     *     "get_formation",
     *     "get_promotion",
     *     "update_promotion",
     *     "add_promotion",
     *     "update_formation"
     * })
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @OA\Property(type="string")
     * @Groups({
     *     "get_etudiant",
     *     "get_formation",
     *     "get_promotion",
     *     "update_formation",
     *     "update_promotion",
     *     "add_promotion"
     * })
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    /**
     * @OA\Property(type="array",
     *      @OA\Items(
     *          @OA\Property(
     *              property="id",
     *              ref="#/components/schemas/Promotion/properties/id"
     *          ),
     *          @OA\Property(
     *              property="nom",
     *              ref="#/components/schemas/Promotion/properties/nom"
     *          ),
     *          @OA\Property(
     *              property="nomPromotion",
     *              ref="#/components/schemas/Promotion/properties/nomPromotion"
     *          )
     *      )
     * )
     * @ORM\OneToMany(targetEntity=Promotion::class, mappedBy="formation" , cascade={"persist"})
     * @Groups({
     *     "get_formation"
     * })
     * @var Promotion
     */
        private $promotions;

    /**
     * @OA\Property(
     *      @OA\Property(
     *          property="id",
     *          ref="#/components/schemas/Responsable/properties/id"
     *      ),
     *      @OA\Property(
     *          property="Personne",
     *          @OA\Property(
     *              property="id",
     *              ref="#/components/schemas/Personne/properties/id"
     *          ),
     *          @OA\Property(
     *              property="nom",
     *              ref="#/components/schemas/Personne/properties/nom"
     *          ),
     *          @OA\Property(
     *              property="prenom",
     *              ref="#/components/schemas/Personne/properties/prenom"
     *          ),
     *      ),
     * ),
     * @ORM\ManyToOne(targetEntity=Responsable::class, inversedBy="formations" , cascade={"persist"})
     * @Groups({
     *     "get_formation",
     *     "update_formation"
     * })
     * @ORM\JoinColumn(nullable=false)
     * @var Responsable
     */
    private $responsable;

    /**
     * @OA\Property(type="boolean")
     * @ORM\Column(type="boolean")
     * @Groups({
     *     "get_etudiant",
     *     "get_promotion",
     *     "get_formation",
     *     "update_formation",
     *     "update_promotion",
     *     "add_promotion"
     * })
     */
    private $isAlternance;

    /*
     * @ORM\ManyToOne(targetEntity=Responsable::class, inversedBy="formations")
     * @ORM\JoinColumn(nullable=false)
     *
    private $respo;*/

    public function __construct()
    {
        $this->promotions = new ArrayCollection();
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
     * @return Collection|Promotion[]
     */
    public function getPromotions(): Collection
    {
        return $this->promotions;
    }

    public function addPromotion(Promotion $promotion): self
    {
        if (!$this->promotions->contains($promotion)) {
            $this->promotions[] = $promotion;
            $promotion->setFormation($this);
        }

        return $this;
    }

    public function removePromotion(Promotion $promotion): self
    {
        if ($this->promotions->removeElement($promotion)) {
            // set the owning side to null (unless already changed)
            if ($promotion->getFormation() === $this) {
                $promotion->setFormation(null);
            }
        }

        return $this;
    }

    public function getResponsable(): ?Responsable
    {
        return $this->responsable;
    }

    public function setResponsable(?Responsable $responsable): self
    {
        $this->responsable = $responsable;

        return $this;
    }

    public function getIsAlternance(): ?bool
    {
        return $this->isAlternance;
    }

    public function setIsAlternance(bool $isAlternance): self
    {
        $this->isAlternance = $isAlternance;

        return $this;
    }

    /*public function getRespo(): ?Responsable
    {
        return $this->responsable;
    }

    public function setRespo(?Responsable $respo): self
    {
        $this->responsable = $respo;

        return $this;
    }*/
}
