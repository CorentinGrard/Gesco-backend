<?php

namespace App\Controller;

use App\Entity\Assistant;
use App\Repository\AssistantRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AssistantController extends AbstractController
{
    /**
     * @Route("/assistants", name="assistant_list")
     */
    public function list(AssistantRepository $assistantRepository): Response
    {
        $assistants = $assistantRepository->findAll();
        $assistantsArray = [];


        foreach($assistants as $assistant){
            array_push($assistantsArray, $assistant->getArray());
        }

        return new JsonResponse($assistantsArray, Response::HTTP_OK);
    }

    /**
     * @Route("/assistants/{id}", name="assistant_list")
     * @param Assistant $assistant
     * @return Response
     */
    public function read(Assistant $assistant): Response
    {
        return new JsonResponse($assistant->getArray(), Response::HTTP_OK);
    }

}
