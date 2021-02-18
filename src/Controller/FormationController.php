<?php

namespace App\Controller;

use App\Repository\FormationRepository;
use App\Repository\PersonneRepository;
use App\Serializers\GenericSerializer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

class FormationController extends AbstractController
{
    /**
     * @Route("/formation", name="formation")
     */
    public function index(): Response
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/FormationController.php',
        ]);
    }

    /**
     * @OA\Delete(
     *      tags={"Formations"},
     *      path="/formations/{idFormation}",
     *      @OA\Parameter(
     *          name="idFormation",
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
     * @Route("/formations/{idFormation}", name="delete_formation", methods={"DELETE"})
     * @param EntityManagerInterface $entityManager
     * @param FormationRepository $formationRepository
     * @param int $idFormation
     * @return JsonResponse
     */
    public function DeleteFormationById(EntityManagerInterface $entityManager, FormationRepository $formationRepository, int $idFormation): JsonResponse
    {
        $repoResponse = $formationRepository->DeleteFormationById($entityManager,$formationRepository, $idFormation);

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


    /**
     * @OA\Get(
     *      tags={"Formations"},
     *      path="/formations",
     *      @OA\Response(
     *          response="200",
     *          description ="get all formations",
     *          @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Formation"))
     *      )
     * )
     * @Route("/formations", name="formation_list", methods={"GET"})
     * @param FormationRepository $formationRepository
     * @return Response
     */
    public function GetAllFormation(FormationRepository $formationRepository) : Response
    {
        $formations = $formationRepository->findAll();

        $json = GenericSerializer::serializeJson($formations,['groups'=>'get_formation']);

        return new JsonResponse($json, Response::HTTP_OK);
    }

    /**
     * @OA\Post(tags={"Formations"},
     *      path="/formations",
     *      @OA\RequestBody(
     *          request="formation",
     *          @OA\JsonContent(
     *              @OA\Property(type="string", property="nameFormation", required = true),
     *              @OA\Property(type="number", property="idResponsable", required = true),
     *              @OA\Property(type="boolean", property="isAlternant", required = true)
     *          )
     *      ),
     *      @OA\Response(response="200", description="Formation ajoutée"),
     *      @OA\Response(response="400", description="Requête invalide"),
     *      @OA\Response(response="404", description="Erreur")
     * )
     * @Route("/formations", methods={"POST"})
     * @param FormationRepository $formationRepository
     * @param PersonneRepository $personneRepository
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function AddFormation(FormationRepository $formationRepository,PersonneRepository $personneRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->getContent(), true);
        $nameFormation = $data['nameFormation'];
        $idResponsable = $data['idResponsable'];
        $isAlternant   = $data['isAlternant'];

        $repoResponse = $formationRepository->AjoutFormation($entityManager, $personneRepository, $nameFormation, $idResponsable, $isAlternant);

        switch ($repoResponse["status"])
        {
            case 404:
                return new JsonResponse($repoResponse["error"],Response::HTTP_NOT_FOUND);
                break;
            case 400:
                return new JsonResponse($repoResponse["error"],Response::HTTP_BAD_REQUEST);
                break;
            case 200:
                return new JsonResponse("Ok",Response::HTTP_CREATED);
                break;
            default:
                return new JsonResponse($repoResponse["error"],Response::HTTP_NOT_FOUND);
        }
    }
}