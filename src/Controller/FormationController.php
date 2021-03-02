<?php

namespace App\Controller;

use App\Repository\FormationRepository;
use App\Repository\PersonneRepository;
use App\Repository\ResponsableRepository;
use App\Serializers\GenericSerializer;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

class FormationController extends AbstractController
{

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
     * @Security("is_granted('ROLE_ADMIN')")
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
     * @param ResponsableRepository $responsableRepository
     * @return Response
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     * @Security("is_granted('ROLE_RESPO') or is_granted('ROLE_ADMIN')")
     */
    public function GetAllFormation(FormationRepository $formationRepository, ResponsableRepository $responsableRepository) : Response
    {
        $user = $this->getUser();
        $username = null;
        if($user != null){
            $roles = $user->getRoles();
            $username = $user->getUsername();
        }

        if(in_array("ROLE_ADMIN",$roles)) {
            $formations = $formationRepository->findAll();

            $json = GenericSerializer::serializeJson($formations, ['groups' => 'get_formation']);

            return new JsonResponse($json, Response::HTTP_OK);
        }
        else if (in_array("ROLE_RESPO",$roles)){
            $responsableConnected = null;
            if (!empty($username))
                $responsableConnected = $responsableRepository->findOneByUsername($username);

            $formations = $formationRepository->findAll();
            $formationsOfRespo = [];
            foreach ($formations as $formation) {
                if ($formation->getResponsable() == $responsableConnected) {
                    array_push($formationsOfRespo, $formation);
                }
            }
            $json = GenericSerializer::serializeJson($formationsOfRespo, ['groups' => 'get_formation']);

            return new JsonResponse($json, Response::HTTP_OK);
        }
        else {
            return new JsonResponse("Problème de rôle", Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(tags={"Formations"},
     *      path="/formations",
     *      @OA\RequestBody(
     *          request="formation",
     *          @OA\JsonContent(
     *              @OA\Property(type="string", property="nom", required = true),
     *              @OA\Property(type="number", property="idResponsable", required = true),
     *              @OA\Property(type="boolean", property="isAlternance", required = true)
     *          )
     *      ),
     *      @OA\Response(response="200", description="Formation ajoutée"),
     *      @OA\Response(response="400", description="Requête invalide"),
     *      @OA\Response(response="404", description="Erreur")
     * )
     * @Route("/formations", methods={"POST"})
     * @param FormationRepository $formationRepository
     * @param ResponsableRepository $responsableRepository
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function AddFormation(FormationRepository $formationRepository, ResponsableRepository $responsableRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->getContent(), true);
        $nameFormation = $data['nom'];
        $idResponsable = $data['idResponsable'];
        $isAlternant   = $data['isAlternance'];

        $repoResponse = $formationRepository->AjoutFormation($entityManager, $responsableRepository, $nameFormation, $idResponsable, $isAlternant);

        switch ($repoResponse["status"])
        {
            case 404:
                return new JsonResponse($repoResponse["error"],Response::HTTP_NOT_FOUND);
                break;
            case 400:
                return new JsonResponse($repoResponse["error"],Response::HTTP_BAD_REQUEST);
                break;
            case 200:
                $json = GenericSerializer::serializeJson($repoResponse["data"], ["groups" => "get_formation"]);
                return new JsonResponse($json,Response::HTTP_CREATED);
                break;
            default:
                return new JsonResponse($repoResponse["error"],Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * @OA\Put(
     *      tags={"Formations"},
     *      path="/formations/{idFormation}",
     *      @OA\Parameter(
     *          name="idFormation",
     *          in="path",
     *          required=true,
     *          description="idPromotion",
     *          @OA\Schema(type="integer")
     *      ),
     *     @OA\RequestBody(
     *          request="formation",
     *          @OA\JsonContent(type="object",
     *              @OA\Property(property="nom",type="string"),
     *              @OA\Property(property="idResponsable",type="number"),
     *              @OA\Property(property="isAlternance",type="boolean")
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *      ),
     *     @OA\Response(
     *         response="404",
     *         description="La formation n'existe pas'",
     *     )
     * )
     * @Route("/formations/{idFormation}", name="update_formation", methods={"PUT"})
     * @param int $idFormation
     * @param FormationRepository $formationRepository
     * @param ResponsableRepository $responsableRepository
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @return JsonResponse
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function UpdateFormation(int $idFormation, FormationRepository $formationRepository,EntityManagerInterface $entityManager,
                                    Request $request, ResponsableRepository $responsableRepository): JsonResponse
    {
        $repoResponse = $formationRepository->UpdateFormation($formationRepository, $entityManager, $idFormation, $request, $responsableRepository);

        switch ($repoResponse["status"]) {
            case 201:
                $json = GenericSerializer::serializeJson($repoResponse["data"], ["groups" => "update_formation"]);
                return new JsonResponse($json,Response::HTTP_CREATED);
                break;
            case 404:
                return new JsonResponse($repoResponse["error"],Response::HTTP_NOT_FOUND);
                break;
            default:
                return new JsonResponse(Response::HTTP_NOT_FOUND);
        }
    }
}