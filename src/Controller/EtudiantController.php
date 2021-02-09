<?php

namespace App\Controller;

use App\Entity\Promotion;
use App\Repository\EtudiantRepository;
use App\Repository\MatiereRepository;
use App\Repository\PromotionRepository;
use App\Serializers\EtudiantSerializer;
use App\Serializers\PromotionSerializer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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


    public function addEtudiantInPromotion(PromotionRepository $promotionRepository,int $idEtudiant, int $idPromotion) {

        $trullyAdded = $promotionRepository->addEtudiantInPromotion($idEtudiant,$idPromotion);

        return new JsonResponse(Response::HTTP_CREATED);

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

}
