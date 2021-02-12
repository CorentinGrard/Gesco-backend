<?php

namespace App\Controller;

use App\Entity\Matiere;
use App\Entity\Promotion;
use App\Repository\EtudiantRepository;
use App\Repository\MatiereRepository;
use App\Repository\PromotionRepository;
use App\Serializers\EtudiantSerializer;
use App\Serializers\PromotionSerializer;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

class EtudiantController extends AbstractController
{
    /**
     * @OA\Get(
     *      tags={"Etudiants"},
     *      path="/etudiants",
     *      @OA\Response(
     *          response="200",
     *          @OA\JsonContent(ref="#/components/schemas/Etudiant")
     *      )
     * )
     * @Route("/etudiants", name="etudiant_list")
     * @param EtudiantRepository $etudiantRepository
     * @return Response
     */
    public function list(EtudiantRepository $etudiantRepository): Response
    {
        $etudiants = $etudiantRepository->findAll();

        $json = EtudiantSerializer::serializeJson($etudiants,['groups'=>'get_etudiant']);

        return new JsonResponse($json, Response::HTTP_OK);

    }

    /**
     * @OA\Get(
     *      tags={"Etudiants"},
     *      path="/promotions/{id}/etudiants",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response="200"
     *      )
     * )
     * @Route("/promotions/{id}/etudiants", name="promotion_etudiants", methods={"GET"})
     * @param PromotionRepository $promotionRepository
     * @param int $id
     * @return JsonResponse
     */
    public function getEtudiantsByPromotion(PromotionRepository $promotionRepository,int $id)
    {
        $promos = $promotionRepository->findBy(['id' => $id]);

        $json = PromotionSerializer::serializeJson($promos, ["groups" => "get_etudiants_by_promotion"]);

        return new JsonResponse($json, Response::HTTP_OK);

    }

    /**
     * @OA\Post(
     *      tags={"Promotions"},
     *      path="/promotion/{idPromotion}/etudiant/{idEtudiant}",
     *      @OA\Parameter(
     *          name="idPromotion",
     *          in="path",
     *          required=true,
     *          description="idPromotion",
     *          @OA\Schema(type="integer")
     *      ),
     *     @OA\Parameter(
     *          name="idEtudiant",
     *          in="path",
     *          required=true,
     *          description="idEtudiant",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response="200",
     *      ),
     *     @OA\Response(
     *         response="401",
     *     ),
     *     @OA\Response(
     *         response="404",
     *     )
     *
     * )
     * @Route("/promotion/{idPromotion}/etudiant/{idEtudiant}", name="add_etudiant_to_promotion", methods={"POST"})
     * @param EntityManagerInterface $entityManager
     * @param EtudiantRepository $etudiantRepository
     * @param PromotionRepository $promotionRepository
     * @param int $idEtudiant
     * @param int $idPromotion
     * @return JsonResponse
     */
    public function addEtudiantInPromotion(EntityManagerInterface $entityManager,EtudiantRepository $etudiantRepository,PromotionRepository $promotionRepository,int $idEtudiant, int $idPromotion) {

        $repoResponse = $promotionRepository->addEtudiantInPromotion($entityManager,$etudiantRepository,$promotionRepository,$idEtudiant,$idPromotion);

        switch ($repoResponse["status"]) {
            case 201:
                return new JsonResponse("Ok",Response::HTTP_CREATED);
                break;
            case 404:
                return new JsonResponse($repoResponse["error"],Response::HTTP_NOT_FOUND);
                break;
            case 406:
                return new JsonResponse($repoResponse["error"],Response::HTTP_NOT_ACCEPTABLE);
                break;
            default:
                return new JsonResponse(Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * @OA\Post(
     *      tags={"Etudiants"},
     *      path="/promotion/{idPromotion}/etudiant",
     *      @OA\Parameter(
     *          name="idPromotion",
     *          in="path",
     *          required=true,
     *          description="idPromotion",
     *          @OA\Schema(type="integer")
     *      ),
     *     @OA\RequestBody(
     *          request="etudiant",
     *          @OA\JsonContent(type="object",
     *              @OA\Property(property="prenom",type="string"),
     *              @OA\Property(property="nom",type="string"),
     *              @OA\Property(property="adresse",type="string"),
     *              @OA\Property(property="numeroTel",type="string"),
     *              @OA\Property(property="isAlternant",type="boolean")
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *      ),
     *     @OA\Response(
     *         response="401",
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="La promotion renseignée n'existe pas",
     *     )
     * )
     * @Route("/promotion/{idPromotion}/etudiant", name="create_etudiant_to_promotion", methods={"POST"})
     * @param int $idPromotion
     * @param PromotionRepository $promotionRepository
     * @param EntityManagerInterface $entityManager
     * @param EtudiantRepository $etudiantRepository
     * @param Request $request
     * @return JsonResponse
     */
    public function createEtudiantInPromotion(int $idPromotion, PromotionRepository $promotionRepository,EntityManagerInterface $entityManager,EtudiantRepository $etudiantRepository, Request $request): JsonResponse
    {

        $promotion = $promotionRepository->find($idPromotion);

        if ($promotion == null) {
            return new JsonResponse("La promotion d'ID ".$idPromotion." renseignée n'existe pas",Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $nom = $data["nom"];
        $prenom = $data["prenom"];
        $adresse = $data["adresse"];
        $numeroTel = $data["numeroTel"];
        $isAlternant = $data["isAlternant"];

        $repoResponse = $etudiantRepository->createEtudiantInPromotion($entityManager,$promotion,$nom,$prenom,$adresse,$numeroTel,$isAlternant);

        switch ($repoResponse["status"]) {
            case 201:
                return new JsonResponse($repoResponse["error"],Response::HTTP_CREATED);
                break;
            case 404:
                return new JsonResponse($repoResponse["error"],Response::HTTP_NOT_FOUND);
                break;
            case 406:
                return new JsonResponse($repoResponse["error"],Response::HTTP_NOT_ACCEPTABLE);
                break;
            default:
                return new JsonResponse(Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * @OA\Put(
     *      tags={"Etudiants"},
     *      path="/etudiant/{idEtudiant}",
     *      @OA\Parameter(
     *          name="idEtudiant",
     *          in="path",
     *          required=true,
     *          description="idPromotion",
     *          @OA\Schema(type="integer")
     *      ),
     *     @OA\RequestBody(
     *          request="etudiant",
     *          @OA\JsonContent(type="object",
     *              @OA\Property(property="prenom",type="string"),
     *              @OA\Property(property="nom",type="string"),
     *              @OA\Property(property="adresse",type="string"),
     *              @OA\Property(property="numeroTel",type="string"),
     *              @OA\Property(property="isAlternant",type="boolean")
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *      ),
     *     @OA\Response(
     *         response="401",
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="L'étudiant n'existe pas en base'",
     *     )
     * )
     * @Route("/etudiant/{idEtudiant}", name="update_etudiant", methods={"PUT"})
     * @param int $idEtudiant
     * @param EtudiantRepository $etudiantRepository
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @return JsonResponse
     */
    public function updateEtudiant(int $idEtudiant, EtudiantRepository $etudiantRepository,EntityManagerInterface $entityManager, Request $request): JsonResponse
    {

        $etudiant = $etudiantRepository->find($idEtudiant);

        if ($etudiant == null) {
            return new JsonResponse("L'étudiant d'ID ".$etudiant." renseignée n'existe pas",Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $nom = $data["nom"];
        $prenom = $data["prenom"];
        $adresse = $data["adresse"];
        $numeroTel = $data["numeroTel"];
        $isAlternant = $data["isAlternant"];

        $repoResponse = $etudiantRepository->updateEtudiant($entityManager,$etudiant,$nom,$prenom,$adresse,$numeroTel,$isAlternant);

        switch ($repoResponse["status"]) {
            case 201:
                return new JsonResponse($repoResponse["error"],Response::HTTP_CREATED);
                break;
            case 404:
                return new JsonResponse($repoResponse["error"],Response::HTTP_NOT_FOUND);
                break;
            case 406:
                return new JsonResponse($repoResponse["error"],Response::HTTP_NOT_ACCEPTABLE);
                break;
            default:
                return new JsonResponse(Response::HTTP_NOT_FOUND);
        }
    }


    /**
     * @OA\Put(
     *      tags={"Promotions"},
     *      path="/promotion/{newIdPromotion}/etudiant/{idEtudiant}",
     *      @OA\Parameter(
     *          name="newIdPromotion",
     *          in="path",
     *          required=true,
     *          description="newIdPromotion",
     *          @OA\Schema(type="integer")
     *      ),
     *     @OA\Parameter(
     *          name="idEtudiant",
     *          in="path",
     *          required=true,
     *          description="idEtudiant",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response="200",
     *      ),
     *     @OA\Response(
     *         response="401",
     *     ),
     *     @OA\Response(
     *         response="404",
     *     )
     *
     * )
     * @Route("/promotion/{newIdPromotion}/etudiant/{idEtudiant}", name="update_etudiant_promotion", methods={"PUT"})
     * @param EntityManagerInterface $entityManager
     * @param EtudiantRepository $etudiantRepository
     * @param PromotionRepository $promotionRepository
     * @param int $idEtudiant
     * @param int $newIdPromotion
     * @return JsonResponse
     */
    public function updateEtudiantPromotion(EntityManagerInterface $entityManager,EtudiantRepository $etudiantRepository,PromotionRepository $promotionRepository,int $idEtudiant, int $newIdPromotion) {

        $repoResponse = $promotionRepository->updateEtudiantPromotion($entityManager,$etudiantRepository,$promotionRepository,$idEtudiant,$newIdPromotion);

        switch ($repoResponse["status"]) {
            case 201:
                return new JsonResponse("Ok",Response::HTTP_CREATED);
                break;
            case 404:
                return new JsonResponse($repoResponse["error"],Response::HTTP_NOT_FOUND);
                break;
            default:
                return new JsonResponse(Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * @OA\Get(
     *      tags={"Etudiants"},
     *      path="/promotions/etudiants",
     *      @OA\Response(
     *          response="200"
     *      )
     * )
     * @Route("/promotions/etudiants", name="promotions_etudiants", methods={"GET"})
     * @param PromotionRepository $promotionRepository
     * @return JsonResponse
     */
    public function getEtudiantsOfAllPromotions(PromotionRepository $promotionRepository) {

        $promotions = $promotionRepository->findAll();

        $json = PromotionSerializer::serializeJson($promotions, ["groups" => "get_etudiants_for_all_promotions"]);

        return new JsonResponse($json,Response::HTTP_OK);

    }

    /**
     * @OA\Delete(
     *      tags={"Etudiants"},
     *      path="/promotion/etudiant/{idEtudiant}",
     *     @OA\Parameter(
     *          name="idEtudiant",
     *          in="path",
     *          required=true,
     *          description="",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response="202",
     *      ),
     *     @OA\Response(
     *         response="404",
     *     )
     *
     * )
     * @Route("/promotion/etudiant/{idEtudiant}", name="delete_etudiant_promotion", methods={"DELETE"})
     * @param EntityManagerInterface $entityManager
     * @param EtudiantRepository $etudiantRepository
     * @param PromotionRepository $promotionRepository
     * @param int $idEtudiant
     * @return JsonResponse
     */
    public function deleteEtudiantFromPromotion(EntityManagerInterface $entityManager,EtudiantRepository $etudiantRepository,PromotionRepository $promotionRepository,int $idEtudiant) {

        $repoResponse = $promotionRepository->deleteEtudiantFromPromotion($entityManager,$etudiantRepository,$idEtudiant);

        switch ($repoResponse["status"]) {
            case 202:
                return new JsonResponse("Ok",Response::HTTP_ACCEPTED);
                break;
            case 404:
                return new JsonResponse($repoResponse["error"],Response::HTTP_NOT_FOUND);
                break;
            default:
                return new JsonResponse(Response::HTTP_NOT_FOUND);
        }
    }

}
