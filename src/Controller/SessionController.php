<?php

namespace App\Controller;

use App\Entity\Session;
use App\Repository\SessionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SessionController extends AbstractController
{
    /**
     * @Route("/sessions", name="session_list")
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
     * @Route("/sessions/{id}", name="session")
     * @param Session $session
     * @return Response
     */
    public function read(Session $session): Response
    {
        return $this->json(
            $session->getArray()
        );
    }
}
