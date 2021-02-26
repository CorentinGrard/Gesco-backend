<?php

namespace App\Controller;

use App\Repository\BatimentRepository;
use App\Repository\SiteRepository;
use App\Serializers\GenericSerializer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

class BatimentController extends AbstractController
{
    /**
     * @Route("/batiment", name="batiment")
     */
    public function index(): Response
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/BatimentController.php',
        ]);
    }

    /**
     * @OA\Post(tags={"Batiments"},
     *      path="/batiments",
     *      @OA\RequestBody(
     *          request="batiment",
     *          @OA\JsonContent(
     *              @OA\Property(type="number", property="idSite", required = true),
     *              @OA\Property(type="string", property="nom", required = true)
     *          )
     *      ),
     *      @OA\Response(response="200", description="Batiment ajouté"),
     *      @OA\Response(response="400", description="Requête invalide"),
     *      @OA\Response(response="404", description="Erreur")
     * )
     * @Route("/batiments", methods={"POST"})
     * @param Request $request
     * @param BatimentRepository $batimentRepository
     * @param SiteRepository $siteRepository
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function AddSite( Request $request, BatimentRepository $batimentRepository, SiteRepository $siteRepository, EntityManagerInterface $entityManager): Response
    {
        $repoResponse = $batimentRepository->AddBatiment($entityManager, $request, $siteRepository);

        switch ($repoResponse["status"]) {
            case 404:
                return new JsonResponse($repoResponse["error"], Response::HTTP_NOT_FOUND);
                break;
            case 201:
                $json = GenericSerializer::serializeJson($repoResponse["data"], ["groups" => "add_batiment"]);
                return new JsonResponse($json, Response::HTTP_CREATED);
                break;
            default:
                return new JsonResponse($repoResponse["error"], Response::HTTP_NOT_FOUND);
        }
    }


}
