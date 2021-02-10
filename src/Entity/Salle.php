<?php

namespace App\Entity;

use App\Repository\SalleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Annotations as OA;
use Symfony\Component\Serializer\Annotation\Groups;



/**
 * @OA\Schema()
 * @ORM\Entity(repositoryClass=SalleRepository::class)
 */
class Salle
{
    /**
     * @OA\Property(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"get_salle","get_session_by_startDate_and_endDate"})
     */
    private $id;

    /**
     * @OA\Property(type="string")
     * @ORM\Column(type="string", length=64)
     * @Groups({"get_salle","get_session_by_startDate_and_endDate"})
     */
    private $nomSalle;

    /**
     * @OA\Property(
     *      @OA\Property(
     *          property="id",
     *          ref="#/components/schemas/Batiment/properties/id"
     *      ),
     *      @OA\Property(
     *          property="nomBatiment",
     *          ref="#/components/schemas/Batiment/properties/nomBatiment"
     *      ),
     *     @OA\Property(
     *          property="batimentSite",
     *          ref="#/components/schemas/Batiment/properties/batimentSite"
     *      ),
     *     readOnly="true"
     * )
     * @OA\Property(type="array", @OA\Items(@OA\Property(property="id", type="integer")))
     * @ORM\ManyToOne(targetEntity=Batiment::class, inversedBy="salles")
     * @Groups({"get_session_by_startDate_and_endDate"})
     */
    private $batiment;

    /**
     * @OA\Property(type="array", @OA\Items(@OA\Property(property="id", type="integer")))
     * @ORM\ManyToMany(targetEntity=Session::class, mappedBy="sessionSalle")
     */
    private $sessions;

    public function __construct()
    {
        $this->sessions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomSalle(): ?string
    {
        return $this->nomSalle;
    }

    public function setNomSalle(string $nomSalle): self
    {
        $this->nomSalle = $nomSalle;

        return $this;
    }

    public function getBatiment(): ?Batiment
    {
        return $this->batiment;
    }

    public function setBatiment(?Batiment $batiment): self
    {
        $this->batiment = $batiment;

        return $this;
    }

    /**
     * @return Collection|Session[]
     */
    public function getSessions(): Collection
    {
        return $this->sessions;
    }

    public function addSession(Session $session): self
    {
        if (!$this->sessions->contains($session)) {
            $this->sessions[] = $session;
            $session->addSessionSalle($this);
        }

        return $this;
    }

    public function removeSession(Session $session): self
    {
        if ($this->sessions->removeElement($session)) {
            $session->removeSessionSalle($this);
        }

        return $this;
    }
}
