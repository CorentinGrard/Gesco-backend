<?php

namespace App\Controller;

use App\Entity\Personne;
use App\Repository\PersonneRepository;
use App\Serializers\PersonneSerializer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

class PersonneController extends AbstractController
{
    /**
     * @OA\Get(
     *      tags={"Personnes"},
     *      path="/personnes",
     *      @OA\Response(
     *          response="200",
     *          @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Personne"))
     *      )
     * )
     * @Route("/personnes", name="personne_list")
     * @param PersonneRepository $personneRepository
     * @return Response
     */
    public function list(PersonneRepository $personneRepository): Response
    {

        $personnes = $personneRepository->findAll();

        $json = PersonneSerializer::serializeJson($personnes,['groups'=>'get_personne']);

        return new JsonResponse($json, Response::HTTP_OK);

    }

    /**
     * @OA\Get(
     *      tags={"Personnes"},
     *      path="/personnes/{id}",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response="200",
     *          @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Semestre"))
     *      )
     * )
     * @Route("/personnes/{id}", name="personne")
     * @param Personne $personne
     * @return Response
     */
    public function getPersonneById(Personne $personne):Response {
        $json = PersonneSerializer::serializeJson($personne,['groups'=>'get_personne']);

        return new JsonResponse($json, Response::HTTP_OK);
    }


}
