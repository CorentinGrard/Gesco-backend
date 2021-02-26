<?php

namespace App\Controller;

use App\Repository\SiteRepository;
use App\Serializers\GenericSerializer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

class SiteController extends AbstractController
{
    /**
     * @Route("/site", name="site")
     */
    public function index(): Response
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/SiteController.php',
        ]);
    }

    /**
     * @OA\Post(tags={"Sites"},
     *      path="/sites",
     *      @OA\RequestBody(
     *          request="site",
     *          @OA\JsonContent(
     *              @OA\Property(type="string", property="nom", required = true),
     *              @OA\Property(type="string", property="adresse", required = true)
     *          )
     *      ),
     *      @OA\Response(response="200", description="Site ajouté"),
     *      @OA\Response(response="400", description="Requête invalide"),
     *      @OA\Response(response="404", description="Erreur")
     * )
     * @Route("/sites", methods={"POST"})
     * @param Request $request
     * @param SiteRepository $siteRepository
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function AddSite( Request $request, SiteRepository $siteRepository ,EntityManagerInterface $entityManager): Response
    {
        $repoResponse = $siteRepository->AddSite($entityManager, $request);

        switch ($repoResponse["status"]) {
            case 404:
                return new JsonResponse($repoResponse["error"], Response::HTTP_NOT_FOUND);
                break;
            case 201:
                $json = GenericSerializer::serializeJson($repoResponse["data"], ["groups" => "add_site"]);
                return new JsonResponse($json, Response::HTTP_CREATED);
                break;
            default:
                return new JsonResponse($repoResponse["error"], Response::HTTP_NOT_FOUND);
        }
    }
}
