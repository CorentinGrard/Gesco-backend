<?php

namespace App\Controller;

use App\Repository\EtudiantRepository;
use App\Serializers\EtudiantSerializer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

class EtudiantController extends AbstractController
{
    /**
     * @OA\Get(
     *      tags={"Etudiants"},
     *      path="/etudiants",
     *      @OA\Response(
     *          response="200",
     *          @OA\JsonContent(ref="#/components/schemas/Etudiant")
     *      )
     * )
     * @Route("/etudiants", name="etudiant_list")
     * @param EtudiantRepository $etudiantRepository
     * @return Response
     */
    public function list(EtudiantRepository $etudiantRepository): Response
    {
        $etudiants = $etudiantRepository->findAll();

        $json = EtudiantSerializer::serializeJson($etudiants,['groups'=>'get_etudiant']);

        return new JsonResponse($json, Response::HTTP_OK);

    }


}
