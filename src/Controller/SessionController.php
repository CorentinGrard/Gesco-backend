<?php

namespace App\Controller;

use App\Entity\Promotion;
use App\Entity\Session;
use App\Repository\MatiereRepository;
use App\Repository\SessionRepository;
use App\Serializers\SessionSerializer;
use App\Tools;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;


class SessionController extends AbstractController
{
    /**
     * @OA\Get(
     *      tags={"Sessions"},
     *      path="/sessions",
     *      @OA\Response(
     *          response="200",
     *          @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Session"))
     *      )
     * )
     * @Route("/sessions", name="session_list", methods={"GET"})
     * @param SessionRepository $sessionRepository
     * @return Response
     */
    public function list(SessionRepository $sessionRepository): Response
    {
        $sessions = $sessionRepository->findAll();

        $json = SessionSerializer::serializeJson($sessions, ['groups'=>'session_get']);

        return new JsonResponse($json, Response::HTTP_OK);

    }

    /**
     * @OA\Get(
     *      tags={"Sessions"},
     *      path="/promos/{id}/sessions/week/{dateString}",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="dateString",
     *          description="AAAAMMJJ",
     *          in="path",
     *          required=false,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response="200",
     *          @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Session"))
     *      )
     * )
     * @Route("/promos/{id}/sessions/week/{dateString}", name="sessions_promo", methods={"GET"}, defaults={"dateString"=""})
     * @param SessionRepository $sessionRepository
     * @param Promotion $promotion
     * @param $dateString
     * @return Response
     */
    public function listWeekPourPromo(SessionRepository $sessionRepository, Promotion $promotion, $dateString): Response
    {
        $dates = Tools::getDatesMonToFri($dateString);

        $sessions = $promotion->getSessions();

        $sessionArray = [];
        foreach($sessions as $session){
            if($session->getDateDebut() >= $dates["debut"] && $session->getDateFin() <= $dates["fin"]){
                array_push($sessionArray, $session);//->getArray());
            }
        }

        $json = SessionSerializer::serializeJson($sessionArray, ['groups'=>'session_get']);

        return new JsonResponse($json, Response::HTTP_OK);
    }


    /**
     * @OA\Get(
     *      tags={"Matieres"},
     *      path="/matieres/{id}",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response="200",
     *          @OA\JsonContent(ref="#/components/schemas/Matiere")
     *      )
     * )
     * @Route("/sessions/{id}", name="session", methods={"GET"})
     * @param Session $session
     * @return Response
     */
    public function read(Session $session): Response
    {
        $json = SessionSerializer::serializeJson($session, ['groups'=>'session_get']);

        return new JsonResponse($json, Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *      tags={"Sessions"},
     *      path="/sessions",
     *      @OA\RequestBody(
     *          request="matieres",
     *          @OA\JsonContent(
     *              @OA\Property(type="string", property="type"),
     *              @OA\Property(type="boolean", property="obligatoire"),
     *              @OA\Property(type="integer", property="matiere", description="idModule"),
     *              @OA\Property(type="string", format="date-time", property="dateDebut"),
     *              @OA\Property(type="string", format="date-time", property="dateFin")
     *          )
     *      ),
     *      @OA\Response(response="201", description="Session ajoutée !"),
     *      @OA\Response(response="404", description="Non trouvé...")
     * )
     * @Route("/sessions", name="add_session", methods={"POST"})
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param MatiereRepository $matiereRepository
     * @return JsonResponse
     * @throws Exception
     */
    public function add(Request $request, EntityManagerInterface $entityManager, MatiereRepository $matiereRepository): JsonResponse
    {
        //TODO Deserialize json posté !
        $data = json_decode($request->getContent(), true);

        $type = $data['type'];
        $obligatoire = $data['obligatoire'];
        $idMatiere = $data['matiere'];
        $dateDebut = $data['dateDebut'];
        $dateFin = $data['dateFin'];

        if (empty($type) || empty($obligatoire) || empty($idMatiere) || empty($dateDebut) || empty($dateFin) ) {
            throw new NotFoundHttpException('Expecting mandatory parameters!');
        }

        $matiere = $matiereRepository->find($idMatiere);

        $session = new Session();
        $session->setType($type);
        $session->setObligatoire($obligatoire);
        $session->setMatiere($matiere);
        $session->setDateDebut(new \DateTime($dateDebut));
        $session->setDateFin(new \DateTime($dateFin));


        $entityManager->persist($session);
        $entityManager->flush();

        return new JsonResponse(['status' => 'Session ajoutée !'], Response::HTTP_CREATED); //TODO

    }


}
