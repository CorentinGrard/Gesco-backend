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
     *      @OA\Response(
     *          response="200",
     *          @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Promotion"))
     *      )
     * )
     * @Route("/assistants/{id}/promotions", name="promos_assistant", methods={"GET"})
     * @param Assistant $assistant
     * @return Response
     */
    public function promosParAssistant(Assistant $assistant):Response
    {
        $promos = $assistant->getPromotions();

        $promoArray = [];

        foreach($promos as $promo){
            array_push($promoArray,$promo->getArray());
        }

        return new JsonResponse($promoArray, Response::HTTP_OK);

    }

    /**
     * @OA\Get(
     *      tags={"Promotions"},
     *      path="/promotions",
     *      @OA\Response(
     *          response="200",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *                  @OA\Property(type="integer", property="id"),
     *                  @OA\Property(
     *                      property="formation",
     *                      @OA\Property(property="id", type="integer")
     *                  ),
     *                  @OA\Property(type="string", property="nomPromotion")
     *              )
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

        $json = PromotionSerializer::serializeJson($promos, ["groups"=>"get_all_promotions"]);

        return new JsonResponse($json, Response::HTTP_OK);
    }
}
