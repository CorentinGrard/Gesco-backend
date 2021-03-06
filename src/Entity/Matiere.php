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
     *      type="integer"
     * )
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({
     *     "matiere_get",
     *     "session_get",
     *     "get_session_by_startDate_and_endDate",
     *     "get_notes_etudiant",
     *     "post_matiere_in_module",
     *     "update_matiere",
     *     "get_intervenant",
     *     "get_intervenant_by_matiere"
     * })
     */
    private $id;

    /**
     * @OA\Property(type="string")
     * @ORM\Column(type="string", length=255)
     * @Groups({
     *     "matiere_get",
     *     "matiere_post",
     *     "session_get",
     *     "get_session_by_startDate_and_endDate",
     *     "get_notes_etudiant",
     *     "post_matiere_in_module",
     *     "update_matiere",
     *     "get_intervenant",
     *     "get_intervenant_by_matiere"
     * })
     */
    private $nom;

    /**
     * @OA\Property(type="integer")
     * @ORM\Column(type="smallint")
     * @Groups({
     *     "matiere_get",
     *     "matiere_post",
     *     "get_session_by_startDate_and_endDate",
     *     "get_notes_etudiant",
     *     "post_matiere_in_module",
     *     "update_matiere"
     * })
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
     * @Groups({"matiere_get", "matiere_post", "get_notes_etudiant","update_matiere"})
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
     * @Groups({"matiere_get","matiere_post","post_matiere_in_module"})
     */
    private $nombreHeuresAPlacer;

    /**
     * @ORM\ManyToMany(targetEntity=Intervenant::class, inversedBy="matieres")
     * @Groups({"matiere_get",
     *     "get_intervenant_by_matiere"})
     * @var Intervenant[]|null
     */
    private $intervenants;

    public function __construct()
    {
        $this->sessions = new ArrayCollection();
        $this->notes = new ArrayCollection();
        $this->intervenants = new ArrayCollection();
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

    /**
     * @return Collection|Intervenant[]
     */
    public function getIntervenants(): Collection
    {
        return $this->intervenants;
    }

    public function addIntervenant(Intervenant $intervenant): self
    {
        if (!$this->intervenants->contains($intervenant)) {
            $this->intervenants[] = $intervenant;
        }

        return $this;
    }

    public function removeIntervenant(Intervenant $intervenant): self
    {
        $this->intervenants->removeElement($intervenant);

        return $this;
    }
}
