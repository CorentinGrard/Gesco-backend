<?php

namespace App\Entity;

use App\Repository\PersonneRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Annotations as OA;
use Symfony\Component\Security\Core\User\UserInterface;
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
class Personne implements UserInterface
{
    /**
     * @OA\Property(type="integer",
     *      readOnly="true")
     * @Groups({
     *     "get_personne",
     *     "get_etudiant",
     *     "get_assistant",
     *     "get_promotion",
     *     "get_formation",
     *     "update_formation",
     *     "get_intervenant",
     *     "matiere_get",
     *     "get_intervenant_by_matiere"
     * })
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @OA\Property(type="string")
     * @Groups({
     *     "get_personne",
     *     "get_etudiant",
     *     "get_assistant",
     *     "get_formation",
     *     "get_promotion",
     *     "get_etudiants_by_promotion",
     *     "get_etudiants_for_all_promotions",
     *     "post_etudiant_in_promotion",
     *     "update_etudiant",
     *     "update_formation",
     *     "update_promotion",
     *     "get_intervenant",
     *     "matiere_get",
     *     "get_intervenant_by_matiere"
     * })
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    /**
     * @OA\Property(type="string")
     * @Groups({
     *     "get_personne",
     *     "get_etudiant",
     *     "get_assistant",
     *     "get_formation",
     *     "get_promotion",
     *     "get_etudiants_by_promotion",
     *     "get_etudiants_for_all_promotions",
     *     "post_etudiant_in_promotion",
     *     "update_etudiant",
     *     "update_formation",
     *     "update_promotion",
     *     "get_intervenant",
     *     "matiere_get",
     *     "get_intervenant_by_matiere"
     * })
     * @ORM\Column(type="string", length=255)
     */
    private $prenom;

    /**
     * @OA\Property(type="string")
     * @Groups({
     *     "get_personne",
     *     "get_etudiant",
     *     "get_assistant",
     *     "get_etudiants_by_promotion",
     *     "get_etudiants_for_all_promotions",
     *     "post_etudiant_in_promotion",
     *     "update_etudiant",
     *     "update_promotion",
     *     "get_intervenant",
     * })
     * @ORM\Column(type="text", length=255, nullable=true)
     */
    private $email;

    /**
     * @OA\Property(type="string")
     * @Groups({
     *     "get_personne",
     *     "get_etudiant",
     *     "get_assistant",
     *     "get_etudiants_by_promotion",
     *     "get_etudiants_for_all_promotions",
     *     "post_etudiant_in_promotion",
     *     "update_etudiant",
     *     "update_promotion"
     * })
     * @ORM\Column(type="string", length=1024, nullable=true)
     */
    private $adresse;

    /**
     * @OA\Property(type="string")
     * @Groups({
     *     "get_personne",
     *     "get_etudiant",
     *     "get_assistant",
     *     "get_etudiants_by_promotion",
     *     "get_etudiants_for_all_promotions",
     *     "post_etudiant_in_promotion",
     *     "update_etudiant",
     *     "update_promotion"
     * })
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $numeroTel;

    /**
     * @OA\Property(type="array",@OA\Items(type="string"))
     * @Groups({"get_personne"})
     * @ORM\Column(type="array")
     */
    private $roles = [];

    /*
     * @ORM\OneToMany(targetEntity=Formation::class, mappedBy="Responsable")
     *
    private $formations;*/

    public function __construct()
    {
        $this->formations = new ArrayCollection();
        $this->roles = ["ROLE_USER"];
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

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

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
            $nom = $this->normalize($this->nom);
            $nom = strtolower(str_replace(" ", "-", $nom));
            $prenom = $this->normalize($this->prenom);
            $prenom = strtolower($prenom);
            $this->email = $prenom . "." . $nom . "@mines-ales" . ($isEtudiant ? ".org" : ".fr");
        }
    }

    function normalize ($string) {
        $table = array(
            'Š'=>'S', 'š'=>'s', 'Đ'=>'Dj', 'đ'=>'dj', 'Ž'=>'Z', 'ž'=>'z', 'Č'=>'C', 'č'=>'c', 'Ć'=>'C', 'ć'=>'c',
            'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
            'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss',
            'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e',
            'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o',
            'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b',
            'ÿ'=>'y', 'Ŕ'=>'R', 'ŕ'=>'r',
        );

        return strtr($string, $table);
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


    public function getRoles(): ?array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /*
     * @return Collection|Formation[]
     *
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
    }*/

    public function getPassword()
    {
        return null;// TODO: Implement getPassword() method.
    }

    public function getSalt()
    {
        return null;// TODO: Implement getSalt() method.
    }

    public function getUsername()
    {
        return $this->email;
    }

    public function eraseCredentials()
    {
        return null;// TODO: Implement eraseCredentials() method.
    }

    /*public function removeFormation(Formation $formation): self
    {
        if ($this->formations->removeElement($formation)) {
            // set the owning side to null (unless already changed)
            if ($formation->getResponsable() === $this) {
                $formation->setResponsable(null);
            }
        }

        return $this;
    }*/
    public function addRole(string $role) :self
    {
        if (!in_array($role, $this->roles)) {
            array_push($this->roles, $role);
        }
        return $this;
    }
}
