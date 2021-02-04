<?php

namespace App\Entity;

use App\Repository\SessionRepository;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Annotations as OA;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @OA\Schema()
 * @ORM\Entity(repositoryClass=SessionRepository::class)
 */
class Session
{
    /**
     * @OA\Property(type="integer",
     *     readOnly="true")
     * @Groups({"matiere_get", "session_get"})
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @OA\Property(
     *      @OA\Property(
     *          property="id",
     *          ref="#/components/schemas/Matiere/properties/id"
     *      ),
     *      @OA\Property(
     *          property="nom",
     *          ref="#/components/schemas/Matiere/properties/nom"
     *      ),
     *     readOnly="true"
     * )
     * @ORM\ManyToOne(targetEntity=Matiere::class, inversedBy="sessions")
     * @Groups({"session_get"})
     */
    private $matiere;

    /**
     * @OA\Property(type="string")
     * @ORM\Column(type="string", length=255)
     * @Groups({"session_get", "session_post"})
     */
    private $type;

    /**
     * @OA\Property(type="boolean")
     * @ORM\Column(type="boolean")
     * @Groups({"session_get", "session_post"})
     */
    private $obligatoire;

    /**
     * @OA\Property(type="string", format="date-time")
     * @ORM\Column(type="datetime")
     * @Groups({"session_get", "session_post"})
     */
    private $dateDebut;

    /**
     * @OA\Property(type="string", format="date-time")
     * @ORM\Column(type="datetime")
     * @Groups({"session_get", "session_post"})
     */
    private $dateFin;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Matiere|null
     */
    public function getMatiere(): ?Matiere
    {
        return $this->matiere;
    }

    /**
     * @param Matiere|null $matiere
     * @return $this
     */
    public function setMatiere(?Matiere $matiere): self
    {
        $this->matiere = $matiere;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getObligatoire(): ?bool
    {
        return $this->obligatoire;
    }

    public function setObligatoire(bool $obligatoire): self
    {
        $this->obligatoire = $obligatoire;

        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeInterface $dateDebut): self
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(\DateTimeInterface $dateFin): self
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    /**
     * @OA\Property(property="duree", type="string", format="date-time",
     *     readOnly="true")
     * @Groups({"session_get", "matiere_get"})
     */
    public function getDuree(): float
    {
        $diff = $this->dateFin->diff($this->dateDebut);

        return $diff->d*24 + $diff->h + ($diff->i/60); /* TODO à tester en conditions réelles */
    }

    public function getArray()
    {
        return [
            "id" => $this->getId(),
            "obligatoire" => $this->getObligatoire(),
            "type" => $this->getType(),
            "dateDebut" => $this->getDateDebut()->format('Y-m-d\TH:i:sO'),
            "dateFin" => $this->getDateFin()->format('Y-m-d\TH:i:sO'),
            "idMatiere" => $this->getMatiere()->getId(),
            "nomMatiere" => $this->getMatiere()->getNom()
        ];
    }

    public function getTypes()
    {
        return \SessionType::values;
    }

}
