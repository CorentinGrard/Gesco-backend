<?php

namespace App\Controller;

use App\Entity\Salle;
use App\Serializers\MatiereSerializer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\SalleRepository;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

class SalleController extends AbstractController
{
    /**
     * @Route("/salle", name="salle")
     */
    public function index(): Response
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/SalleController.php',
        ]);
    }

    /**
     * @OA\Get(
     *      tags={"Salles"},
     *      path="/salles",
     *      @OA\Response(
     *          response="200",
     *          description ="Salles",
     *          @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Salle"))
     *      )
     *
     * )
     * @Route("/salles", name="salle_list", methods={"GET"})
     * @param SalleRepository $salleRepository
     * @return Response
     */
    public function list(SalleRepository $salleRepository): Response
    {

        $salles = $salleRepository->findAllSalle();

        $json = MatiereSerializer::serializeJson($salles,['groups'=>'get_salle']);
        return new JsonResponse($json, Response::HTTP_OK);

    }
}
