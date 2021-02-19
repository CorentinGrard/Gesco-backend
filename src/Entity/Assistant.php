<?php

namespace App\Entity;

use App\Repository\AssistantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use OpenApi\Annotations as OA;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;


/**
 * @OA\Schema(
 *      schema="Assistant",
 * )
 * @ORM\Entity(repositoryClass=AssistantRepository::class)
 */
class Assistant
{
    /**
     * @OA\Property(type="integer",
     *      readOnly="true")
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"get_assistant", "get_promotion","update_promotion"})
     */
    private $id;

    /**
     * @OA\Property(
     *      property="personne",
     *      allOf={@OA\Schema(ref="#/components/schemas/Personne")}
     * )
     * @ORM\OneToOne(targetEntity=Personne::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"get_assistant","update_promotion"})
     */
    private $Personne;

    /**
     * @OA\Property(type="array",
     *      @OA\Items(
     *          @OA\Property(
     *              property="id",
     *              ref="#/components/schemas/Promotion/properties/id"
     *          ),
     *          @OA\Property(
     *              property="nomPromotion",
     *              ref="#/components/schemas/Promotion/properties/nomPromotion"
     *          )
     *      ),
     *      readOnly="true"
     * )
     * @ORM\OneToMany(targetEntity=Promotion::class, mappedBy="assistant")
     * @Groups({"get_assistant"})
     */
    private $promotions;

    public function __construct()
    {
        $this->promotions = new ArrayCollection();
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

        $this->Personne->addRole("ROLE_ASSISTANT");

        return $this;
    }

    public function getArray(){
        return $this->Personne->getArray();
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
            $promotion->setAssistant($this);
        }

        return $this;
    }

    public function removePromotion(Promotion $promotion): self
    {
        if ($this->promotions->removeElement($promotion)) {
            // set the owning side to null (unless already changed)
            if ($promotion->getAssistant() === $this) {
                $promotion->setAssistant(null);
            }
        }

        return $this;
    }
}
