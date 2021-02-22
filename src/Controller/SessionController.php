<?php

namespace App\Controller;

use App\Entity\Matiere;
use App\Entity\Promotion;
use App\Entity\Session;
use App\Repository\AssistantRepository;
use App\Repository\EtudiantRepository;
use App\Repository\MatiereRepository;
use App\Repository\PersonneRepository;
use App\Repository\PromotionRepository;
use App\Repository\SessionRepository;
use App\Serializers\SessionSerializer;
use App\Tools;
use DateTime;
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
     * @param LoggerInterface $logger
     * @param AssistantRepository $assistantRepository
     * @param PersonneRepository $personneRepository
     * @return Response
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_ASSISTANT')")
     */
    public function list(SessionRepository $sessionRepository, LoggerInterface $logger, AssistantRepository $assistantRepository, PersonneRepository $personneRepository): Response
    {
        $sessions = $sessionRepository->findAll();

        /*$user = $this->getUser();
        if($user != null){
            $username = $user->getUsername();
            if(!empty($username)){
                $personne = $assistantRepository->findOneByUsername($username);
                if($personne != null){
                    $logger->debug("Assistant trouvé !!! => " . $personne->getPersonne()->getEmail());
                    foreach($personne->getPromotions() as $promotion)
                    {
                        array_push($sessions, $promotion->getFormation()->)
                    }
                }else{
                    $admin = $personneRepository->findOneByUsername($username);
                    if(in_array("ROLE_ADMIN", $personne->getRoles()))
                    {
                        $logger->debug("Admin trouvé !!! => " . $personne->getEmail());
                        $sessions = $sessionRepository->findAll();
                    } else
                    {
                        $logger->debug("Admin non trouvé !!!");
                        return new JsonResponse("Admin non trouvé", Response::HTTP_FORBIDDEN);
                    }

                }
            }
        }*/


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
        if ($endDate == null) {
            return new JsonResponse("La date de fin n'est pas valable", Response::HTTP_NOT_ACCEPTABLE);
        }

        $sessionArray = $sessionRepository->allSessionsBetweenStartDateAndEndDateForPromotion($promotion,$startDate,$endDate);

        $json = SessionSerializer::serializeJson($sessionArray, ['groups'=>'get_session_by_startDate_and_endDate']);

        return new JsonResponse($json, Response::HTTP_OK);
    }


    /**
     * @OA\Get(
     *      tags={"Sessions"},
     *      path="/etudiants/start/{startDateString}/end/{endDateString}/sessions",
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
     *          description ="Les sessions pour cet étudiant aux dates demandées sont trouvées",
     *          @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Session"))
     *      ),
     *     @OA\Response(
     *          response="403",
     *          description ="L'utilisateur n'existe pas|L'utilisateur n'est pas un étudiant"
     *      ),
     *     @OA\Response(
     *          response="406",
     *          description ="La date de début ou de fin n'est pas valide"
     *      ),
     * )
     * @Route("/etudiants/start/{startDateString}/end/{endDateString}/sessions", name="get_session_by_startDate_and_endDate_etudiant", methods={"GET"})
     * @param SessionRepository $sessionRepository
     * @param PromotionRepository $promotionRepository
     * @param string $startDateString
     * @param string $endDateString
     * @return Response
     * @Security("is_granted('ROLE_ETUDIANT')")
     */
    public function allSessionsBetweenStartDateAndEndDateForEtudiant(SessionRepository $sessionRepository, EtudiantRepository $etudiantRepository, string $startDateString, string $endDateString): Response
    {
        $user = $this->getUser();
        if($user == null)
            return new JsonResponse("L'utilisateur n'existe pas", Response::HTTP_FORBIDDEN);

        $username = $user->getUsername();
        if(empty($username))
            return new JsonResponse("L'utilisateur n'existe pas", Response::HTTP_FORBIDDEN);

        $etudiant = $etudiantRepository->findOneByUsername($username);
        if($etudiant == null)
            return new JsonResponse("L'utilisateur n'est pas un étudiant", Response::HTTP_FORBIDDEN);


        $startDate = Tools::getDateByStringDate($startDateString);
        if ($startDate == null) {
            return new JsonResponse("La date de début n'est pas valable (AAAAMMJJ)", Response::HTTP_NOT_ACCEPTABLE);
        }

        $endDate = Tools::getDateByStringDate($endDateString);
        if ($endDate == null) {
            return new JsonResponse("La date de fin n'est pas valable (AAAAMMJJ)", Response::HTTP_NOT_ACCEPTABLE);
        }

        $sessionArray = $sessionRepository->allSessionsBetweenStartDateAndEndDateForPromotion($etudiant->getPromotion(),$startDate,$endDate);

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
     *          @OA\JsonContent(
     *              @OA\Property(property="type", ref="#/components/schemas/Session/properties/type"),
     *              @OA\Property(property="obligatoire", ref="#/components/schemas/Session/properties/obligatoire"),
     *              @OA\Property(property="dateDebut", ref="#/components/schemas/Session/properties/dateDebut"),
     *              @OA\Property(property="dateFin", ref="#/components/schemas/Session/properties/dateFin"),
     *              @OA\Property(property="detail", ref="#/components/schemas/Session/properties/detail"),
     *          )
     *      ),
     *      @OA\Response(response="201", description="Session ajoutée !"),
     *      @OA\Response(response="409", description="Erreur...")
     * )
     * @Route("/matieres/{id}/sessions", name="add_session", methods={"POST"})
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param Matiere $matiere
     * @param LoggerInterface $logger
     * @return JsonResponse
     * Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_ASSISTANT')")
     */
    public function add(Request $request, EntityManagerInterface $entityManager, Matiere $matiere, LoggerInterface $logger): JsonResponse
    {
        //TODO Deserialize json posté !
        $data = json_decode($request->getContent(), true);

        $type = $data['type'];
        $obligatoire = $data['obligatoire'];
        $dateDebut = $data['dateDebut'];
        $dateFin = $data['dateFin'];
        $detail = $data['detail'];

        if (empty($type) || empty($obligatoire) || empty($dateDebut) || empty($dateFin) || empty($detail)) {
            throw new NotFoundHttpException('Paramètres obligatoires attendus !');
        }

        try {
            $session = new Session();
            $session->setType($type);
            $session->setObligatoire($obligatoire);
            $session->setMatiere($matiere);
            $session->setDateDebut(new DateTime($dateDebut));
            $session->setDateFin(new DateTime($dateFin));
            $session->setDetail($detail);

            /*$session = SessionSerializer::deSerializeJson($data, ['groups'=>'create_session']);

            $logger->warning("Session type : " . $session->getType());//Matiere()->getNom());*/

            $entityManager->persist($session);
            $entityManager->flush();

            $json = SessionSerializer::serializeJson($session, ['groups'=>'session_get']);
            return new JsonResponse($json, Response::HTTP_CREATED);

        }catch (Exception $exception){
            return new JsonResponse("Erreur", Response::HTTP_CONFLICT);
        }


    }

    /**
     * @OA\Put(
     *      tags={"Sessions"},
     *      path="/sessions/{id}",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="id Session",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          request="sessions",
     *          @OA\JsonContent(
     *              @OA\Property(property="type", ref="#/components/schemas/Session/properties/type"),
     *              @OA\Property(property="obligatoire", ref="#/components/schemas/Session/properties/obligatoire"),
     *              @OA\Property(property="dateDebut", ref="#/components/schemas/Session/properties/dateDebut"),
     *              @OA\Property(property="dateFin", ref="#/components/schemas/Session/properties/dateFin"),
     *              @OA\Property(property="detail", ref="#/components/schemas/Session/properties/detail"),
     *              @OA\Property(
     *                  property="idMatiere",
     *                  ref="#/components/schemas/Matiere/properties/id",
     *              )
     *          )
     *      ),
     *      @OA\Response(response="201", description="Session modifiée !"),
     *      @OA\Response(response="404", description="Non trouvée..."),
     *      description="idMatiere : 0 => On ne change pas la matière"
     * )
     * @Route("/sessions/{id}", name="update_session", methods={"PUT"})
     * @param Request $request
     * @param SessionRepository $sessionRepository
     * @param EntityManagerInterface $entityManager
     * @param Session $session
     * @param MatiereRepository $matiereRepository
     * @param int $idMatiere
     * @return JsonResponse
     */
    public function modifySession(Request $request, SessionRepository  $sessionRepository, EntityManagerInterface $entityManager, Session $session, MatiereRepository $matiereRepository): JsonResponse
    {
        //TODO Deserialize json posté !
        $data = json_decode($request->getContent(), true);

        $type = $data['type'];
        $idMatiere = $data['idMatiere'];
        $obligatoire = $data['obligatoire'];
        $dateDebut = $data['dateDebut'];
        $dateFin = $data['dateFin'];
        $detail = $data['detail'];

        if (empty($type) || empty($obligatoire) || empty($dateDebut) || empty($dateFin) || empty($detail)) {
            throw new NotFoundHttpException('Paramètres obligatoires attendus !');
        }

        if(empty($idMatiere))
            $idMatiere = 0;

        $matiere = null;
        $matiere = $matiereRepository->find($idMatiere);
        if($matiere == null){
            $matiere =  $session->getMatiere();
        }

        $repoResponse = $sessionRepository->updateSession($entityManager,$session, $type,  $obligatoire, $dateDebut, $dateFin, $detail, $matiere);

        $json = SessionSerializer::serializeJson($repoResponse, ['groups'=>'session_get']);
        return new JsonResponse($json, Response::HTTP_CREATED); //TODO

    }

    /**
     * @OA\Delete(
     *      tags={"Sessions"},
     *      path="/sessions/{id}",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="id Session",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(response="200", description="Session correctement supprimée"),
     *      @OA\Response(response="404", description="Session non trouvée...")
     * )
     * @Route("/sessions/{id}", name="delete_session", methods={"DELETE"})
     * @param SessionRepository $sessionRepository
     * @param EntityManagerInterface $entityManager
     * @param Session|null $session
     * @return JsonResponse
     */
    public function deleteSession(SessionRepository  $sessionRepository, EntityManagerInterface $entityManager, Session $session = null): JsonResponse
    {
        if($session == null){
            return new JsonResponse("Session non trouvée...", Response::HTTP_NOT_FOUND);
        }

        $repoResponse = $sessionRepository->deleteSession($entityManager, $session);

        return new JsonResponse($repoResponse["data"], Response::HTTP_OK);
    }

}
