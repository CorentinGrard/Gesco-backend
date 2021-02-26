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

    /**
     * @OA\Get(
     *      tags={"Sites"},
     *      path="/sites",
     *      @OA\Response(
     *          response="200",
     *          description ="get all sites",
     *          @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Site"))
     *      )
     * )
     * @Route("/sites", name="site_list", methods={"GET"})
     * @param SiteRepository $siteRepository
     * @return Response
     */
    public function GetAllSite(SiteRepository $siteRepository) : Response
    {
        $formations = $siteRepository->findAll();

        $json = GenericSerializer::serializeJson($formations,['groups'=>'get_site']);

        return new JsonResponse($json, Response::HTTP_OK);
    }

    /**
     * @OA\Put(
     *      tags={"Sites"},
     *      path="/sites/{idSite}",
     *      @OA\Parameter(
     *          name="idSite",
     *          in="path",
     *          required=true,
     *          description="idSite",
     *          @OA\Schema(type="integer")
     *      ),
     *     @OA\RequestBody(
     *          request="formation",
     *          @OA\JsonContent(type="object",
     *              @OA\Property(property="nom",type="string"),
     *              @OA\Property(property="adresse",type="string")
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *      ),
     *     @OA\Response(
     *         response="404",
     *         description="Le site n'existe pas'",
     *     )
     * )
     * @Route("/sites/{idSite}", name="update_site", methods={"PUT"})
     * @param int $idSite
     * @param SiteRepository $siteRepository
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @return JsonResponse
     */
    public function UpdateSite(int $idSite, EntityManagerInterface $entityManager, SiteRepository $siteRepository, Request $request ): JsonResponse
    {
        $repoResponse = $siteRepository->UpdateSite($siteRepository, $entityManager, $idSite, $request);

        switch ($repoResponse["status"]) {
            case 201:
                $json = GenericSerializer::serializeJson($repoResponse["data"], ["groups" => "update_site"]);
                return new JsonResponse($json,Response::HTTP_CREATED);
                break;
            case 404:
                return new JsonResponse($repoResponse["error"],Response::HTTP_NOT_FOUND);
                break;
            case 400:
                return new JsonResponse($repoResponse["error"],Response::HTTP_BAD_REQUEST);
                break;
            default:
                return new JsonResponse(Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * @OA\Delete(
     *      tags={"Sites"},
     *      path="/sites/{idSite}",
     *      @OA\Parameter(
     *          name="idSite",
     *          in="path",
     *          required=true,
     *          description ="",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response="202",
     *      ),
     *      @OA\Response(
     *          response="404",
     *      ),
     *      @OA\Response(
     *          response="409",
     *      )
     * )
     * @Route("/sites/{idSite}", name="delete_site", methods={"DELETE"})
     * @param EntityManagerInterface $entityManager
     * @param SiteRepository $siteRepository
     * @param int $idSite
     * @return JsonResponse
     */
    public function DeleteSiteById(EntityManagerInterface $entityManager, SiteRepository $siteRepository, int $idSite): JsonResponse
    {
        $repoResponse = $siteRepository->DeleteSiteById($entityManager,$siteRepository, $idSite);

        switch ($repoResponse["status"]){
            case 200:
                return new JsonResponse("Ok",Response::HTTP_ACCEPTED);
                break;
            case 404:
                return new JsonResponse($repoResponse["error"],Response::HTTP_NOT_FOUND);
                break;
            case 409:
                return new JsonResponse($repoResponse["error"],Response::HTTP_CONFLICT);
                break;
            default:
                return new JsonResponse(Response::HTTP_NOT_FOUND);
        }
    }
}
