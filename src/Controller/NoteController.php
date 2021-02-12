<?php

namespace App\Controller;

use App\Repository\EtudiantRepository;
use App\Repository\MatiereRepository;
use App\Repository\NoteRepository;
use App\Repository\PersonneRepository;
use App\Serializers\EtudiantSerializer;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

class NoteController extends AbstractController
{
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
 *              description ="Notes",
     *          @OA\JsonContent(type="object",
     *              @OA\Property(property="id", type="integer"),
     *              @OA\Property(property="nom", type="string"),
     *              @OA\Property(property="modules", type="array",
     *                  @OA\Items(
     *                      @OA\Schema(schema="module",
     *                          @OA\Property(property="id", type="integer"),
     *                          @OA\Property(property="nom", type="string"),
     *                          @OA\Property(property="matieres", type="array",
     *                              @OA\Items(
     *                                  @OA\Schema(schema="matiere",
     *                                      @OA\Property(property="id", type="integer"),
     *                                          @OA\Property(property="nom", type="string"),
     *                                          @OA\Property(property="note", type="string"),
     *                                          @OA\Property(property="coeff", type="string")
     *
     *                                  )
     *                              )
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
     *      path="/etudiants/{idEtudiant}/notes",
     *      @OA\Parameter(
     *          name="idEtudiant",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response="200",
     *          @OA\JsonContent(type="object",
     *              @OA\Property(property="id", type="integer"),
     *              @OA\Property(property="nom", type="string"),
     *              @OA\Property(property="modules", type="array",
     *                  @OA\Items(
     *                          @OA\Property(property="id", type="integer"),
     *                          @OA\Property(property="nom", type="string"),
     *                          @OA\Property(property="matieres", type="array",
     *                                  @OA\Items(
     *                                      @OA\Property(property="id", type="integer"),
     *                                      @OA\Property(property="nom", type="string"),
     *                                      @OA\Property(property="note", type="string"),
     *                                      @OA\Property(property="coeff", type="string")
     *
     *                                  )
     *
     *                          )
     *
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(response="404", description="Non trouvé...")
     * )
     * @Route("/etudiants/{idEtudiant}/notes")
     * @param NoteRepository $noteRepository
     * @param EtudiantRepository $etudiantRepository
     * @param integer $idEtudiant
     * @param LoggerInterface $logger
     * @return JsonResponse
     * Security("is_granted('ROLE_ETUDIANT')")
     */
    public function notesEtudiant(NoteRepository $noteRepository, EtudiantRepository $etudiantRepository, int $idEtudiant, LoggerInterface $logger)
    {
            $user = $this->getUser();
            if($user != null){
                $username = $user->getUsername();
                if(!empty($username))
                    $etudiant = $etudiantRepository->findOneByUsername($username);
            }


        $notes = $noteRepository->getAllNotes($idEtudiant);
        return new JsonResponse($notes, Response::HTTP_OK);
    }


    /**
     * @OA\Get(
     *      tags={"Notes"},
     *      path="/etudiants/notes",
     *      @OA\Response(response="200",
     *          @OA\JsonContent(
     *              @OA\Property(property="id", ref="#/components/schemas/Etudiant/properties/id"),
     *              @OA\Property(property="notes", type="array",
     *                  @OA\Items(
     *                      @OA\Property(property="id", ref="#/components/schemas/Note/properties/id"),
     *                      @OA\Property(property="note", ref="#/components/schemas/Note/properties/note"),
     *                      @OA\Property(property="matiere",
     *                          @OA\Property(property="id", ref="#/components/schemas/Matiere/properties/id"),
     *                          @OA\Property(property="nom", ref="#/components/schemas/Matiere/properties/nom"),
     *                          @OA\Property(property="coefficient", ref="#/components/schemas/Matiere/properties/coefficient")
     *                      )
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(response="404", description="Non trouvé...")
     * )
     * @Route("/etudiants/notes")
     * @param NoteRepository $noteRepository
     * @param EtudiantRepository $etudiantRepository
     * @param integer $idEtudiant
     * @param LoggerInterface $logger
     * @return JsonResponse
     * @Security("is_granted('ROLE_ETUDIANT')")
     */
    public function notesEtudiantConnected(NoteRepository $noteRepository, EtudiantRepository $etudiantRepository, LoggerInterface $logger)
    {
        //$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();
        if($user != null){
            $username = $user->getUsername();
            if(!empty($username))
            {
                $etudiant = $etudiantRepository->findOneByUsername($username);
                $json = EtudiantSerializer::serializeJson($etudiant, ["groups"=> "get_notes_etudiant"]);
                return new JsonResponse($json, Response::HTTP_OK);
            }

        }
        return new JsonResponse("Étudiant ou notes non trouvés", Response::HTTP_NOT_FOUND);

    }


}
