<?php

namespace App\Controller;

use App\Entity\Assistant;
use App\Entity\Promotion;
use App\Repository\PromotionRepository;
use App\Serializers\PromotionSerializer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;


class PromotionController extends AbstractController
{
    /**
     * @OA\Get(
     *      tags={"Promotions"},
     *      path="/assistants/{id}/promotions",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response="200",
     *          @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Promotion"))
     *      )
     * )
     * @Route("/assistants/{id}/promotions", name="assistant_promotions", methods={"GET"})
     * @param Assistant $assistant
     * @return Response
     */
    public function promosParAssistant(Assistant $assistant):Response
    {
        $promos = $assistant->getPromotions();

        /*$promoArray = [];

        foreach($promos as $promo){
            array_push($promoArray,$promo->getArray());
        }*/

        $json = PromotionSerializer::serializeJson($promos, ["groups"=>"get_promotion"]);

        return new JsonResponse($json, Response::HTTP_OK);

    }

    /**
     * @OA\Get(
     *      tags={"Promotions"},
     *      path="/promotions",
     *      @OA\Response(
     *          response="200",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/Promotion")
     *          )
     *      )
     * )
     * @Route("/promotions", name="promotions", methods={"GET"})
     * @param PromotionRepository $promotionRepository
     * @return Response
     */
    public function getAllPromotions(PromotionRepository $promotionRepository):Response
    {
        $promos = $promotionRepository->findAll();

        $json = PromotionSerializer::serializeJson($promos, ["groups"=>"get_promotion"]);

        return new JsonResponse($json, Response::HTTP_OK);
    }
}
