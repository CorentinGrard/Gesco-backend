<?php

namespace App\Controller;

use App\Entity\Intervenant;
use App\Entity\Matiere;
use App\Repository\IntervenantRepository;
use App\Serializers\GenericSerializer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

class IntervenantController extends AbstractController
{
    /**
     * @OA\Get(
     *      tags={"Intervenants"},
     *      path="/intervenants",
     *      @OA\Response(
     *          response="200",
     *          @OA\JsonContent(ref="#/components/schemas/Intervenant")
     *      )
     * )
     * @Route("/intervenants", name="list_intervenants")
     * @param IntervenantRepository $intervenantRepository
     * @return Response
     */
    public function list(IntervenantRepository $intervenantRepository): Response
    {
        $intervenants = $intervenantRepository->findAll();

        $json = GenericSerializer::serializeJson($intervenants, ["groups"=>"get_intervenant"]);

        return new JsonResponse($json, Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *      tags={"Intervenants"},
     *      path="/intervenants/{id}",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response="200",
     *          @OA\JsonContent(ref="#/components/schemas/Intervenant")
     *      )
     * )
     * @Route("/intervenants/{id}", name="list_intervenants")
     * @param Intervenant|null $intervenant
     * @return Response
     */
    public function read(Intervenant $intervenant = null): Response
    {
        if($intervenant == null){
            return new JsonResponse("Non trouvé", Response::HTTP_NOT_FOUND);
        }

        $json = GenericSerializer::serializeJson($intervenant, ["groups"=>"get_intervenant"]);

        return new JsonResponse($json, Response::HTTP_OK);
    }


    /**
     * @OA\Get(
     *      tags={"Intervenants"},
     *      path="/matieres/{id}/intervenants",
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
     * @Route("/matieres/{id}/intervenants", name="list_intervenants")
     * @param Matiere|null $matiere
     * @return Response
     */
    public function intervenantsParMatiere(Matiere $matiere = null): Response
    {
        if($matiere == null){
            return new JsonResponse("Non trouvé", Response::HTTP_NOT_FOUND);
        }

        $json = GenericSerializer::serializeJson($matiere, ["groups"=>"get_intervenant_by_matiere"]);

        return new JsonResponse($json, Response::HTTP_OK);
    }

}
