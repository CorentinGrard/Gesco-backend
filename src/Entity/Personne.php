<?php

namespace App\Entity;

use App\Repository\PersonneRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Annotations as OA;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @OA\Schema(
 *     schema="Personne"
 * )
 * @ORM\Entity(repositoryClass=PersonneRepository::class)
 * @ORM\MappedSuperclass
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorMap({"personne" = "Personne", "etudiant" = "Etudiant"})
 */
class Personne
{
    /**
     * @OA\Property(type="integer",
     *      readOnly="true")
     * @Groups({"get_personne", "get_etudiant", "get_assistant", "get_promotion", "get_etudiants_by_promotion","get_etudiants_for_all_promotions"})
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @OA\Property(type="string")
     * @Groups({"get_personne", "get_etudiant", "get_assistant", "get_promotion","get_etudiants_by_promotion","get_etudiants_for_all_promotions"})
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    /**
     * @OA\Property(type="string")
     * @Groups({"get_personne", "get_etudiant", "get_assistant", "get_promotion", "get_etudiants_by_promotion","get_etudiants_for_all_promotions"})
     * @ORM\Column(type="string", length=255)
     */
    private $prenom;

    /**
     * @OA\Property(type="string",
     *      readOnly="true")
     * @Groups({"get_personne", "get_etudiant", "get_assistant","get_etudiants_by_promotion","get_etudiants_for_all_promotions"})
     * @ORM\Column(type="text", length=255)
     */
    private $email;

    /**
     * @OA\Property(type="string")
     * @Groups({"get_personne", "get_etudiant", "get_assistant","get_etudiants_by_promotion","get_etudiants_for_all_promotions"})
     * @ORM\Column(type="string", length=1024, nullable=true)
     */
    private $adresse;

    /**
     * @OA\Property(type="string")
     * @Groups({"get_personne", "get_etudiant", "get_assistant","get_etudiants_by_promotion","get_etudiants_for_all_promotions"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $numeroTel;

    /**
     * @ORM\OneToMany(targetEntity=Formation::class, mappedBy="Responsable")
     */
    private $formations;

    public function __construct()
    {
        $this->formations = new ArrayCollection();
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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    /*public function set Email(string $email): self
    {
        $this->email = $email;

        return $this;
    }*/

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): self
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getNumeroTel(): ?string
    {
        return $this->numeroTel;
    }

    public function setNumeroTel(?string $numeroTel): self
    {
        $this->numeroTel = $numeroTel;

        return $this;
    }

    //TODO Créer la fonction de génération d'email ?

    public function generateEmail(bool $isEtudiant = true){
        if(!isset($this->email)){
            $this->email = strtolower($this->prenom) . "." . strtolower(str_replace(" ", "-", $this->nom)) . "@mines-ales" . ($isEtudiant ? ".org" : ".fr");
        }
    }

    public function getArray()
    {
        return [
            "id" => $this->getId(),
            "nom" => $this->getNom(),
            "prenom" => $this->getPrenom(),
            "email" => $this->getEmail(),
            "adresse" => $this->getAdresse(),
            "numeroTel" => $this->getNumeroTel()
        ];
    }

    /**
     * @return Collection|Formation[]
     */
    public function getFormations(): Collection
    {
        return $this->formations;
    }

    public function addFormation(Formation $formation): self
    {
        if (!$this->formations->contains($formation)) {
            $this->formations[] = $formation;
            $formation->setResponsable($this);
        }

        return $this;
    }

    public function removeFormation(Formation $formation): self
    {
        if ($this->formations->removeElement($formation)) {
            // set the owning side to null (unless already changed)
            if ($formation->getResponsable() === $this) {
                $formation->setResponsable(null);
            }
        }

        return $this;
    }
}
