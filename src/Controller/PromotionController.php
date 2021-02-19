<?php

namespace App\Controller;

use App\Entity\Assistant;
use App\Entity\Formation;
use App\Entity\Promotion;
use App\Repository\AssistantRepository;
use App\Repository\FormationRepository;
use App\Repository\PersonneRepository;
use App\Repository\PromotionRepository;
use App\Serializers\GenericSerializer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
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

        $json = GenericSerializer::serializeJson($promos, ["groups"=>"get_promotion"]);

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

        $json = GenericSerializer::serializeJson($promos, ["groups"=>"get_promotion"]);

        return new JsonResponse($json, Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *      tags={"Promotions"},
     *      path="/formations/{id}/promotions",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response="200",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/Promotion")
     *          )
     *      )
     * )
     * @Route("/formations/{id}/promotions", name="promotions_by_formation", methods={"GET"})
     * @param Formation $formation
     * @return Response
     * Security("is_granted('ROLE_ADMIN')")
     */
    public function getPromotionsByFormation(Formation $formation):Response
    {
        $promotions = $formation->getPromotions();

        $json = GenericSerializer::serializeJson($promotions, ["groups"=>"get_promotion"]);

        return new JsonResponse($json, Response::HTTP_OK);
    }


    /**
     * @OA\Get(
     *      tags={"Promotions"},
     *      path="/responsables/promotions",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response="200",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/Promotion")
     *          )
     *      )
     * )
     * @Route("/responsables/promotions", name="promotions_by_responsable", methods={"GET"})
     * @param PersonneRepository $personneRepository
     * @return Response
     * @Security("is_granted('ROLE_RESPO')")
     */
    public function getPromotionsForRespo(PersonneRepository $personneRepository):Response
    {
        $promotions = [];
        $user = $this->getUser();
        if($user != null){
            $username = $user->getUsername();
            if(!empty($username)){
                $personne = $personneRepository->findOneByUsername($username);
                $formations = $personne->getFormations();
                foreach ($promotions as $promotion)
                {
                    array_push($promotions, $promotion);
                }
            }
        }

        $json = GenericSerializer::serializeJson($promotions, ["groups"=>"get_promotion"]);

        return new JsonResponse($json, Response::HTTP_OK);
    }

    /**
     * @OA\Post(tags={"Promotions"},
     *      path="/promotions",
     *      @OA\RequestBody(
     *          request="promotion",
     *          @OA\JsonContent(
     *              @OA\Property(type="number", property="idFormation", required = true),
     *              @OA\Property(type="number", property="idAssistant", required = true),
     *              @OA\Property(type="string", property="namePromotion", required = true)
     *          )
     *      ),
     *      @OA\Response(response="200", description="Promotion ajoutée"),
     *      @OA\Response(response="400", description="Requête invalide"),
     *      @OA\Response(response="404", description="Erreur")
     * )
     * @Route("/promotions", methods={"POST"})
     * @param FormationRepository $formationRepository
     * @param PromotionRepository $promotionRepository
     * @param AssistantRepository $assistantRepository
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function AddFormation(FormationRepository $formationRepository, PromotionRepository $promotionRepository, AssistantRepository $assistantRepository, Request $request, EntityManagerInterface $entityManager): Response
    {

        $repoResponse = $promotionRepository->AjoutPromotion($entityManager, $formationRepository, $promotionRepository, $assistantRepository , $request);

        switch ($repoResponse["status"])
        {
            case 404:
                return new JsonResponse($repoResponse["error"],Response::HTTP_NOT_FOUND);
                break;
            case 200:
                return new JsonResponse("Ok",Response::HTTP_CREATED);
                break;
            default:
                return new JsonResponse($repoResponse["error"],Response::HTTP_NOT_FOUND);
        }
    }
}
