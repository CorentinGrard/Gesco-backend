<?php

namespace App\Controller;

use App\Entity\Session;
use App\Repository\MatiereRepository;
use App\Repository\SessionRepository;
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
        $idMatiere = $data['idMmatiere'];
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
        $session->setDateDebut($dateDebut);
        $session->setDateFin($dateFin);


        $entityManager->persist($session);
        $entityManager->flush();

        return new JsonResponse(['status' => 'Session ajoutée !'], Response::HTTP_CREATED); //TODO

    }


}
