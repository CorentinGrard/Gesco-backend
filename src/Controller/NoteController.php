<?php

namespace App\Controller;

use App\Entity\Etudiant;
use App\Repository\EtudiantRepository;
use App\Repository\MatiereRepository;
use App\Repository\NoteRepository;
use App\Serializers\EtudiantSerializer;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

class NoteController extends AbstractController
{
    // TODO à revoir
    /**
     * @Route("/note", name="note")
     */
    public function index(): Response
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/NoteController.php',
        ]);
    }

    // TODO finir refactor POST '/notes'
    /**
     * @OA\Post(tags={"Notes"},
     *      path="/notes",
     *      @OA\RequestBody(
     *          request="notes",
     *          @OA\JsonContent(
     *              @OA\Property(type="integer", property="idMatiere"),
     *              @OA\Property(type="integer", property="idEtudiant"),
     *              @OA\Property(type="number", format="float", property="note")
     *          )
     *      ),
     *      @OA\Response(response="201", description="Note ajoutée !"),
     *      @OA\Response(response="404", description="Non trouvé...")
     * )
     * @Route("/notes", methods={"POST"}, requirements={"idEtudiant"="\d+", "idMatiere"="\d+"})
     * @param NoteRepository $noteRepository
     * @param MatiereRepository $matiereRepository
     * @param EtudiantRepository $etudiantRepository
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function ajoutNoteAEtudiantDansUneMatiere(NoteRepository $noteRepository, MatiereRepository $matiereRepository, EtudiantRepository $etudiantRepository, Request $request, EntityManagerInterface $entityManager): Response
    {

        $data = json_decode($request->getContent(), true);
        $idMatiere = $data['idMatiere'];
        $idEtudiant = $data['idEtudiant'];
        $note = $data['note'];


        if (empty($idMatiere) || empty($idEtudiant) || empty($note)) {
            throw new NotFoundHttpException('Expecting mandatory parameters!');
        }

        try {
            $noteRepository->ajoutNoteEtudiant($entityManager, $etudiantRepository, $matiereRepository, $idEtudiant, $idMatiere, $note);
        } catch (OptimisticLockException $e) {
            throw $e;
        } catch (ORMException $e) {
            throw $e;
        }

        return new JsonResponse(['status' => 'Note ajoutée !'], Response::HTTP_CREATED);
    }

// TODO refactor '/etudiants/{idEtudiant}/semestres/{idSemestre}/notes'
    /**
     * @OA\Get(
     *      tags={"Notes"},
     *      path="/etudiants/{idEtudiant}/semestres/{idSemestre}/notes",
     *      @OA\Parameter(
     *          name="idEtudiant",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="idSemestre",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response="200",
     *          @OA\JsonContent(
     *              @OA\Property(property="id", ref="#/components/schemas/Personne/properties/id"),
     *              @OA\Property(property="nom", ref="#/components/schemas/Personne/properties/nom"),
     *              @OA\Property(property="modules", type="array",
     *                  @OA\Items(
     *                      @OA\Property(property="id", ref="#/components/schemas/Module/properties/id"),
     *                      @OA\Property(property="nom", ref="#/components/schemas/Module/properties/nom"),
     *                      @OA\Property(property="matieres", type="array",
     *                          @OA\Items(
     *                              @OA\Property(property="id", ref="#/components/schemas/Matiere/properties/id"),
     *                              @OA\Property(property="nom", ref="#/components/schemas/Matiere/properties/nom"),
     *                              @OA\Property(property="note", ref="#/components/schemas/Note/properties/note"),
     *                              @OA\Property(property="coefficient", ref="#/components/schemas/Matiere/properties/coefficient")
     *                          )
     *                      )
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(response="404", description="Non trouvé...")
     * )
     * @Route("/etudiants/{idEtudiant}/semestres/{idSemestre}/notes")
     * @param NoteRepository $noteRepository
     * @param integer $idEtudiant
     * @param integer $idSemestre
     * @return JsonResponse
     */
    public function notesEtudiantParSemestre(NoteRepository $noteRepository, $idEtudiant, $idSemestre)
    {
        $notesSemestres = $noteRepository->getAllNotesBySemestre($idEtudiant, $idSemestre);
        return new JsonResponse($notesSemestres, Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *      tags={"Notes"},
     *      path="/etudiants/{id}/notes",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="id Etudiant",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response="200",
     *          @OA\JsonContent(
     *              @OA\Property(property="Personne",
     *                  @OA\Property(property="nom", ref="#/components/schemas/Personne/properties/nom"),
     *                  @OA\Property(property="prenom", ref="#/components/schemas/Personne/properties/prenom")
     *              ),
     *              @OA\Property(property="Notes", type="array",
     *                  @OA\Items(
     *                      @OA\Property(property="id", ref="#/components/schemas/Note/properties/id"),
     *                      @OA\Property(property="note", ref="#/components/schemas/Note/properties/note"),
     *                      @OA\Property(property="Matiere",
     *                          @OA\Property(property="id", ref="#/components/schemas/Matiere/properties/id"),
     *                          @OA\Property(property="nom", ref="#/components/schemas/Matiere/properties/nom"),
     *                          @OA\Property(property="coefficient", ref="#/components/schemas/Matiere/properties/coefficient"),
     *                          @OA\Property(property="module",
     *                              @OA\Property(property="id", ref="#/components/schemas/Module/properties/id"),
     *                              @OA\Property(property="nom", ref="#/components/schemas/Module/properties/nom"),
     *                              @OA\Property(property="semestre",
     *                                  @OA\Property(property="id", ref="#/components/schemas/Semestre/properties/id"),
     *                                  @OA\Property(property="nom", ref="#/components/schemas/Semestre/properties/nom"),
     *                              ),
     *                          ),
     *                      ),
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(response="404", description="Non trouvé...")
     * )
     * @Route("/etudiants/{id}/notes")
     * @param Etudiant $etudiant
     * @return JsonResponse
     */
    public function notesEtudiant(/*NoteRepository $noteRepository,*/ Etudiant $etudiant)
    {
        //$notes = $noteRepository->getAllNotes($etudiant->getId());
        //return new JsonResponse($notes, Response::HTTP_OK);

        $json = EtudiantSerializer::serializeJson($etudiant, ["groups"=>"notes_etudiant"]);
        return new JsonResponse($json, Response::HTTP_OK);
    }


}
