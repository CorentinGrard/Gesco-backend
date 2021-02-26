<?php

namespace App\Entity;

use App\Repository\SiteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Annotations as OA;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @OA\Schema()
 * @ORM\Entity(repositoryClass=SiteRepository::class)
 */
class Site
{
    /**
     * @OA\Property(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({
     *     "get_session_by_startDate_and_endDate",
     *     "add_site",
     *     "get_site",
     *     "update_site"
     * })
     */
    private $id;

    /**
     * @OA\Property(type="string")
     * @ORM\Column(type="string", length=64)
     * @Groups({
     *     "get_session_by_startDate_and_endDate",
     *     "add_site", "get_site",
     *     "update_site"
     * })
     */
    private $nomSite;

    /**
     * @OA\Property(type="string")
     * @ORM\Column(type="string", length=255)
     * @Groups({
     *     "get_session_by_startDate_and_endDate",
     *     "add_site", "get_site","update_site"
     * })
     */
    private $adress;

    /**
     * @ORM\OneToMany(targetEntity=Batiment::class, mappedBy="batimentSite", orphanRemoval=true)
     */
    private $batiments;

    public function __construct()
    {
        $this->batiments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomSite(): ?string
    {
        return $this->nomSite;
    }

    public function setNomSite(string $nomSite): self
    {
        $this->nomSite = $nomSite;

        return $this;
    }

    public function getAdress(): ?string
    {
        return $this->adress;
    }

    public function setAdress(string $adress): self
    {
        $this->adress = $adress;

        return $this;
    }

    /**
     * @return Collection|Batiment[]
     */
    public function getBatiments(): Collection
    {
        return $this->batiments;
    }

    public function addBatiment(Batiment $batiment): self
    {
        if (!$this->batiments->contains($batiment)) {
            $this->batiments[] = $batiment;
            $batiment->setBatimentSite($this);
        }

        return $this;
    }

    public function removeBatiment(Batiment $batiment): self
    {
        if ($this->batiments->removeElement($batiment)) {
            // set the owning side to null (unless already changed)
            if ($batiment->getBatimentSite() === $this) {
                $batiment->setBatimentSite(null);
            }
        }

        return $this;
    }
}
