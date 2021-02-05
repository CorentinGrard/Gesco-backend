<?php

namespace App\Controller;

use App\Repository\EtudiantRepository;
use App\Repository\MatiereRepository;
use App\Repository\NoteRepository;
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
}
