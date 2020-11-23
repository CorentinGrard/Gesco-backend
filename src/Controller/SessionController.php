<?php

namespace App\Controller;

use App\Entity\Promotion;
use App\Entity\Session;
use App\Repository\MatiereRepository;
use App\Repository\PromotionRepository;
use App\Repository\SessionRepository;
use App\Tools;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class SessionController extends AbstractController
{
    /**
     * @Route("/sessions", name="session_list", methods={"GET"})
     * @param SessionRepository $sessionRepository
     * @return Response
     */
    public function list(SessionRepository $sessionRepository): Response
    {
        $sessions = $sessionRepository->findAll();
        $sessionArray = [];


        foreach($sessions as $session){
            array_push($sessionArray, $session->getArray());
        }

        return $this->json(
            $sessionArray
        );
    }

    /**
     * @Route("/promo/{id}/sessions/week/{dateString}", name="session_promo", methods={"GET"}, defaults={"dateString"=""})
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
                array_push($sessionArray, $session->getArray());
            }
        }

        return $this->json(
            [
                "status"=>200,
                "result"=>$sessionArray,
                "info"=>
                    [
                        "dates"=>$dates
                    ]
            ]
        );
    }


    /**
     * @Route("/sessions/{id}", name="session", methods={"GET"})
     * @param Session $session
     * @return Response
     */
    public function read(Session $session): Response
    {
        return $this->json(
            $session->getArray()
        );
    }

    /**
     * @Route("/sessions", name="add_session", methods={"POST"})
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param MatiereRepository $matiereRepository
     * @return JsonResponse
     */
    public function add(Request $request, EntityManagerInterface $entityManager, MatiereRepository $matiereRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $type = $data['type'];
        $obligatoire = $data['obligatoire'];
        $idMatiere = $data['idMatiere'];
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
