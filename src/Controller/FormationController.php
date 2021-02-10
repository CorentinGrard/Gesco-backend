<?php

namespace App\Controller;

use App\Repository\FormationRepository;
use App\Repository\PersonneRepository;
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
     * @OA\Post(tags={"Formations"},
     *      path="/formations",
     *      @OA\RequestBody(
     *          request="formation",
     *          @OA\JsonContent(
     *              @OA\Property(type="string", property="nameFormation", required = true),
     *              @OA\Property(type="number", property="idResponsable", required = true)
     *          )
     *      ),
     *      @OA\Response(response="201", description="Formation ajoutée"),
     *      @OA\Response(response="404", description="Erreur")
     * )
     * @Route("/formations", methods={"POST"})
     * @param FormationRepository $formationRepository
     * @param PersonneRepository $personneRepository
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function ajoutNoteAEtudiantDansUneMatiere(FormationRepository $formationRepository,PersonneRepository $personneRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->getContent(), true);
        $nameFormation = $data['nameFormation'];
        $idResponsable = $data['idResponsable'];

        $repoResponse = $formationRepository->ajoutFormation($entityManager, $personneRepository, $nameFormation, $idResponsable);

        switch ($repoResponse["status"])
        {
            case 404:
                return new JsonResponse($repoResponse["error"],Response::HTTP_NOT_FOUND);
                break;
            case 201:
                return new JsonResponse("Ok",Response::HTTP_CREATED);
                break;
            default:
                return new JsonResponse($repoResponse["error"],Response::HTTP_NOT_FOUND);
        }
    }
}
