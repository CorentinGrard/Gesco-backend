<?php

namespace App\Controller;

use App\Entity\Matiere;
use App\Entity\Promotion;
use App\Entity\Session;
use App\Repository\EtudiantRepository;
use App\Repository\MatiereRepository;
use App\Repository\PromotionRepository;
use App\Repository\SessionRepository;
use App\Serializers\SessionSerializer;
use App\Tools;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
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
     *          description ="Session",
     *          @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Session"))
     *      )
     * )
     * @Route("/sessions", name="session_list", methods={"GET"})
     * @param SessionRepository $sessionRepository
     * @return Response
     * @Security("is_granted('ROLE_ETUDIANT')")
     */
    public function list(SessionRepository $sessionRepository, LoggerInterface $logger, EtudiantRepository $etudiantRepository): Response
    {
        $sessions = $sessionRepository->findAll();

        $user = $this->getUser();
        if($user != null){
            $username = $user->getUsername();
            if(!empty($username)){
                $etudiant = $etudiantRepository->findOneByUsername($username);
                if($etudiant != null){
                    $logger->debug("Etudiant trouvé !!! => " . $etudiant->getPersonne()->getEmail());
                }
            }
        }

        $json = SessionSerializer::serializeJson($sessions, ['groups'=>'session_get']);

        return new JsonResponse($json, Response::HTTP_OK);

    }

    /**
     * @OA\Get(
     *      tags={"Sessions"},
     *      path="/promotions/{id}/sessions/week/{dateString}",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="id Promotion",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="dateString",
     *          description="format AAAAMMJJ",
     *          in="path",
     *          required=false,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description ="Sessions",
     *          @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Session"))
     *      )
     * )
     * @Route("/promotions/{id}/sessions/week/{dateString}", name="sessions_promo", methods={"GET"}, defaults={"dateString"=""})
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
     *      tags={"Sessions"},
     *      path="/promotions/{idPromotion}/start/{startDateString}/end/{endDateString}/sessions",
     *      @OA\Parameter(
     *          name="idPromotion",
     *          in="path",
     *          required=true,
     *          description="id Promotion",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="startDateString",
     *          description="format AAAAMMJJ",
     *          in="path",
     *          required=false,
     *          @OA\Schema(type="integer")
     *      ),
     *     @OA\Parameter(
     *          name="endDateString",
     *          description="format AAAAMMJJ",
     *          in="path",
     *          required=false,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description ="Les sessions pour cette promotion aux dates demandées sont trouvées (Attention, example incomplet)",
     *          @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Session"))
     *      ),
     *     @OA\Response(
     *          response="404",
     *          description ="L'ID Promotion demandé n'existe pas"
     *      ),
     *     @OA\Response(
     *          response="406",
     *          description ="La date de début ou de fin n'est pas valide"
     *      ),
     * )
     * @Route("/promotions/{idPromotion}/start/{startDateString}/end/{endDateString}/sessions", name="get_session_by_startDate_and_endDate", methods={"GET"})
     * @param SessionRepository $sessionRepository
     * @param PromotionRepository $promotionRepository
     * @param int $idPromotion
     * @param string $startDateString
     * @param string $endDateString
     * @return Response
     */
    public function allSessionsBetweenStartDateAndEndDateForPromotion(SessionRepository $sessionRepository, PromotionRepository $promotionRepository, int $idPromotion, string $startDateString, string $endDateString): Response
    {
        $promotion = $promotionRepository->find($idPromotion);

        if ($promotion == null) {
            return new JsonResponse("La promotion n'existe pas", Response::HTTP_NOT_FOUND);
        }

        $startDate = Tools::getDateByStringDate($startDateString);
        if ($startDate == null) {
            return new JsonResponse("La date de début n'est pas valable", Response::HTTP_NOT_ACCEPTABLE);
        }

        $endDate = Tools::getDateByStringDate($endDateString);
        if ($startDate == null) {
            return new JsonResponse("La date de fin n'est pas valable", Response::HTTP_NOT_ACCEPTABLE);
        }

        $sessionArray = $sessionRepository->allSessionsBetweenStartDateAndEndDateForPromotion($promotion,$startDate,$endDate);

        $json = SessionSerializer::serializeJson($sessionArray, ['groups'=>'get_session_by_startDate_and_endDate']);

        return new JsonResponse($json, Response::HTTP_OK);
    }


    /**
     * @OA\Get(
     *      tags={"Sessions"},
     *      path="/sessions/{id}",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response="200",
     *          @OA\JsonContent(ref="#/components/schemas/Session")
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
     *      path="/matieres/{id}/sessions",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="id Matiere",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          request="sessions",
     *          @OA\JsonContent(ref="#/components/schemas/Session")
     *      ),
     *      @OA\Response(response="201", description="Session ajoutée !"),
     *      @OA\Response(response="404", description="Non trouvée...")
     * )
     * @Route("/matieres/{id}/sessions", name="add_session", methods={"POST"})
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param Matiere $matiere
     * @return JsonResponse
     * @throws Exception
     */
    public function add(Request $request, EntityManagerInterface $entityManager, Matiere $matiere): JsonResponse
    {
        //TODO Deserialize json posté !
        $data = json_decode($request->getContent(), true);

        $type = $data['type'];
        $obligatoire = $data['obligatoire'];
        $dateDebut = $data['dateDebut'];
        $dateFin = $data['dateFin'];
        $detail = $data['detail'];

        if (empty($type) || empty($obligatoire) || empty($dateDebut) || empty($dateFin) || empty($detail)) {
            throw new NotFoundHttpException('Expecting mandatory parameters!');
        }

        $session = new Session();
        $session->setType($type);
        $session->setObligatoire($obligatoire);
        $session->setMatiere($matiere);
        $session->setDateDebut(new \DateTime($dateDebut));
        $session->setDateFin(new \DateTime($dateFin));
        $session->setDetail($detail);


        $entityManager->persist($session);
        $entityManager->flush();

        $json = SessionSerializer::serializeJson($session, ['groups'=>'create_session']);
        return new JsonResponse($json, Response::HTTP_CREATED);

    }

    /**
     * @OA\Put(
     *      tags={"Sessions"},
     *      path="/session/{id}",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="id Session",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          request="sessions",
     *          @OA\JsonContent(ref="#/components/schemas/Session")
     *      ),
     *      @OA\Response(response="201", description="Session modifiée !"),
     *      @OA\Response(response="404", description="Non trouvée...")
     * )
     * @Route("/session/{id}", name="update_session", methods={"PUT"})
     * @param Request $request
     * @param SessionRepository $sessionRepository
     * @param EntityManagerInterface $entityManager
     * @param Session $session
     * @return JsonResponse
     */
    public function modifySession(Request $request, SessionRepository  $sessionRepository, EntityManagerInterface $entityManager, Session $session): JsonResponse
    {
        //TODO Deserialize json posté !
        $data = json_decode($request->getContent(), true);

        $type = $data['type'];
        $obligatoire = $data['obligatoire'];
        $dateDebut = $data['dateDebut'];
        $dateFin = $data['dateFin'];
        $detail = $data['detail'];

        if (empty($type) || empty($obligatoire) || empty($dateDebut) || empty($dateFin) || empty($detail)) {
            throw new NotFoundHttpException('Expecting mandatory parameters!');
        }

        $repoResponse = $sessionRepository->updateSession($entityManager,$session, $type,  $obligatoire, $dateDebut, $dateFin, $detail);

        $json = SessionSerializer::serializeJson($repoResponse, ['groups'=>'update_session']);
        return new JsonResponse($json, Response::HTTP_CREATED); //TODO

    }

    /**
     * @OA\Delete(
     *      tags={"Sessions"},
     *      path="/session/{id}",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="id Session",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          request="sessions",
     *          @OA\JsonContent(ref="#/components/schemas/Session")
     *      ),
     *      @OA\Response(response="201", description="Session supprimée !"),
     *      @OA\Response(response="404", description="Non trouvée...")
     * )
     * @Route("/session/{id}", name="delete_session", methods={"DELETE"})
     * @param Request $request
     * @param SessionRepository $sessionRepository
     * @param EntityManagerInterface $entityManager
     * @param Session $session
     * @return JsonResponse
     */
    public function deleteSession(Request $request, SessionRepository  $sessionRepository, EntityManagerInterface $entityManager, Session $session): JsonResponse
    {

        $repoResponse = $sessionRepository->deleteSession($entityManager,$session);

        $json = SessionSerializer::serializeJson($session, ['groups'=>'delete_session']);
        return new JsonResponse($json, Response::HTTP_CREATED);

    }

}
