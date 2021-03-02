<?php

namespace App\Controller;

use App\Entity\Salle;
use App\Repository\BatimentRepository;
use App\Serializers\GenericSerializer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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

        $json = GenericSerializer::serializeJson($salles,['groups'=>'get_salle']);
        return new JsonResponse($json, Response::HTTP_OK);

    }

    /**
     * @OA\Post(tags={"Salles"},
     *      path="/salles",
     *      @OA\RequestBody(
     *          request="salle",
     *          @OA\JsonContent(
     *              @OA\Property(type="number", property="idBatiment", required = true),
     *              @OA\Property(type="string", property="nom", required = true)
     *          )
     *      ),
     *      @OA\Response(response="200", description="Salle ajoutée"),
     *      @OA\Response(response="400", description="Requête invalide"),
     *      @OA\Response(response="404", description="Erreur")
     * )
     * @Route("/salles", methods={"POST"})
     * @param Request $request
     * @param SalleRepository $salleRepository
     * @param BatimentRepository $batimentRepository
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function AddSalle( Request $request, BatimentRepository $batimentRepository, SalleRepository $salleRepository, EntityManagerInterface $entityManager): Response
    {
        $repoResponse = $salleRepository->AddSalle($entityManager, $request, $batimentRepository);

        switch ($repoResponse["status"]) {
            case 404:
                return new JsonResponse($repoResponse["error"], Response::HTTP_NOT_FOUND);
                break;
            case 201:
                $json = GenericSerializer::serializeJson($repoResponse["data"], ["groups" => "add_salle"]);
                return new JsonResponse($json, Response::HTTP_CREATED);
                break;
            default:
                return new JsonResponse($repoResponse["error"], Response::HTTP_NOT_FOUND);
        }
    }
}
