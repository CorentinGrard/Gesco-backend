<?php

namespace App\Entity;

use App\Repository\MatiereRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Annotations as OA;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @OA\Schema()
 * @ORM\Entity(repositoryClass=MatiereRepository::class)
 */
class Matiere
{
    /**
     * @OA\Property(
     *      type="integer",
     *      readOnly="true"
     * )
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"matiere_get", "session_get"})
     */
    private $id;

    /**
     * @OA\Property(type="string")
     * @ORM\Column(type="string", length=255)
     * @Groups({"matiere_get", "matiere_post", "session_get"})
     */
    private $nom;

    /**
     * @OA\Property(type="integer")
     * @ORM\Column(type="smallint")
     * @Groups({"matiere_get", "matiere_post"})
     */
    private $coefficient;

    /**
     * @OA\Property(type="array",
     *      @OA\Items(
     *          @OA\Property(
     *              property="id",
     *              ref="#/components/schemas/Session/properties/id"
     *          ),
     *          @OA\Property(
     *              property="duree",
     *              ref="#/components/schemas/Session/properties/duree"
     *          )
     *      ),
     *      readOnly="true"
     * )
     * @ORM\OneToMany(targetEntity=Session::class, mappedBy="matiere", cascade={"remove"})
     * @Groups("matiere_get")
     */
    private $sessions;

    /**
     * @OA\Property(
     *      @OA\Property(
     *          property="id",
     *          ref="#/components/schemas/Module/properties/id"
     *      ),
     *      @OA\Property(
     *          property="nom",
     *          ref="#/components/schemas/Module/properties/nom"
     *      ),
     *      readOnly="true"
     * )
     * @ORM\ManyToOne(targetEntity=Module::class, inversedBy="matieres",cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"matiere_get", "matiere_post"})
     */
    private $module;

    /**
     * @OA\Property(type="array",
     *      @OA\Items(
     *          @OA\Property(
     *              property="id",
     *              ref="#/components/schemas/Note/properties/id",
     *          ),
     *          @OA\Property(
     *              property="note",
     *              ref="#/components/schemas/Note/properties/note"
     *          )
     *      ),
     *      readOnly="true"
     * )
     * @ORM\OneToMany(targetEntity=Note::class, mappedBy="Matiere")
     * @Groups({"matiere_get"})
     */
    private $notes;

    /**
     * @OA\Property(type="integer")
     * @ORM\Column(type="integer")
     * @Groups({"matiere_get", "matiere_post"})
     */
    private $nombreHeuresAPlacer;

    public function __construct()
    {
        $this->sessions = new ArrayCollection();
        $this->notes = new ArrayCollection();
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

    public function getCoefficient(): ?int
    {
        return $this->coefficient;
    }

    public function setCoefficient(int $coefficient): self
    {
        $this->coefficient = $coefficient;

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
            $session->setMatiere($this);
        }

        return $this;
    }

    public function removeSession(Session $session): self
    {
        if ($this->sessions->removeElement($session)) {
            // set the owning side to null (unless already changed)
            if ($session->getMatiere() === $this) {
                $session->setMatiere(null);
            }
        }

        return $this;
    }

/*    public function getArray()
    {
        $sessions = [];
        foreach($this->getSessions() as $session){
            array_push($sessions, $session->getId());
        }
        //TO DO $notes

        return [
            "id" => $this->getId(),
            "nom" => $this->getNom(),
            "idModule" => $this->getModule()->getId(),
            "coefficient" => $this->getCoefficient(),
            "idSessions" => $sessions
            //TO DO idNotes
        ];
    }*/

    public function getModule(): ?Module
    {
        return $this->module;
    }

    public function setModule(?Module $module): self
    {
        $this->module = $module;

        return $this;
    }

    /**
     * @return Collection|Note[]
     */
    public function getNotes(): Collection
    {
        return $this->notes;
    }

    public function addNote(Note $note): self
    {
        if (!$this->notes->contains($note)) {
            $this->notes[] = $note;
            $note->setMatiere($this);
        }

        return $this;
    }

    public function removeNote(Note $note): self
    {
        if ($this->notes->removeElement($note)) {
            // set the owning side to null (unless already changed)
            if ($note->getMatiere() === $this) {
                $note->setMatiere(null);
            }
        }

        return $this;
    }

    public function getNombreHeuresAPlacer(): ?int
    {
        return $this->nombreHeuresAPlacer;
    }

    public function setNombreHeuresAPlacer(int $nombreHeuresAPlacer): self
    {
        $this->nombreHeuresAPlacer = $nombreHeuresAPlacer;

        return $this;
    }

    /* TODO getNombreHeuresPlacees() + serialization */
}
