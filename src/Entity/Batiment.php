<?php

namespace App\Entity;

use App\Repository\BatimentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Annotations as OA;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @OA\Schema()
 * @ORM\Entity(repositoryClass=BatimentRepository::class)
 */
class Batiment
{
    /**
     * @OA\Property(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"get_session_by_startDate_and_endDate", "add_batiment"})
     */
    private $id;

    /**
     * @OA\Property(type="string")
     * @ORM\Column(type="string", length=64)
     * @Groups({"get_session_by_startDate_and_endDate", "add_batiment"})
     */
    private $nomBatiment;

    /**
     * @OA\Property(
     *      @OA\Property(
     *          property="id",
     *          ref="#/components/schemas/Site/properties/id"
     *      ),
     *      @OA\Property(
     *          property="nomSite",
     *          ref="#/components/schemas/Site/properties/nomSite"
     *      ),
     *     @OA\Property(
     *          property="adress",
     *          ref="#/components/schemas/Site/properties/adress"
     *      ),
     *     readOnly="true"
     * )
     * @ORM\ManyToOne(targetEntity=Site::class, inversedBy="batiments")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"get_session_by_startDate_and_endDate", "add_batiment"})
     */
    private $batimentSite;

    /**
     * @ORM\OneToMany(targetEntity=Salle::class, mappedBy="batiment")
     */
    private $salles;

    public function __construct()
    {
        $this->salles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomBatiment(): ?string
    {
        return $this->nomBatiment;
    }

    public function setNomBatiment(string $nomBatiment): self
    {
        $this->nomBatiment = $nomBatiment;

        return $this;
    }

    public function getBatimentSite(): ?Site
    {
        return $this->batimentSite;
    }

    public function setBatimentSite(?Site $batimentSite): self
    {
        $this->batimentSite = $batimentSite;

        return $this;
    }

    /**
     * @return Collection|Salle[]
     */
    public function getSalles(): Collection
    {
        return $this->salles;
    }

    public function addSalle(Salle $salle): self
    {
        if (!$this->salles->contains($salle)) {
            $this->salles[] = $salle;
            $salle->setBatiment($this);
        }
        return $this;
    }

    public function removeSalle(Salle $salle): self
    {
        if ($this->salles->removeElement($salle)) {
            // set the owning side to null (unless already changed)
            if ($salle->getBatiment() === $this) {
                $salle->setBatiment(null);
            }
        }
        return $this;
    }
}
