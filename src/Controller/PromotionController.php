<?php

namespace App\Controller;

use App\Entity\Assistant;
use App\Entity\Formation;
use App\Entity\Promotion;
use App\Repository\AssistantRepository;
use App\Repository\FormationRepository;
use App\Repository\PersonneRepository;
use App\Repository\PromotionRepository;
use App\Repository\ResponsableRepository;
use App\Serializers\GenericSerializer;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
     * @param ResponsableRepository $responsableRepository
     * @param AssistantRepository $assistantRepository
     * @return Response
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     * @Security("is_granted('ROLE_RESPO') or is_granted('ROLE_ASSISTANT') or is_granted('ROLE_ADMIN')")
     */
    public function getAllPromotions(PromotionRepository $promotionRepository, ResponsableRepository $responsableRepository, AssistantRepository $assistantRepository):Response
    {
        $user = $this->getUser();
        $username = null;
        if($user != null){
            $roles = $user->getRoles();
            $username = $user->getUsername();
        }

        if(in_array("ROLE_ADMIN",$roles)) {
            $promos = $promotionRepository->findAll();

            $json = GenericSerializer::serializeJson($promos, ["groups" => "get_promotion"]);

            return new JsonResponse($json, Response::HTTP_OK);
        }
        else if (in_array("ROLE_RESPO",$roles)) {

            $responsableConnected = null;
            if (!empty($username))
                $responsableConnected = $responsableRepository->findOneByUsername($username);

            $promos = $promotionRepository->findAll();
            $promoOfRespo = [];
            foreach ($promos as $promo) {
                if ($promo->getFormation()->getResponsable() === $responsableConnected) {
                    array_push($promoOfRespo,$promo);
                }
            }

            $json = GenericSerializer::serializeJson($promoOfRespo, ["groups" => "get_promotion"]);
            return new JsonResponse($json, Response::HTTP_OK);
        }
        else if (in_array("ROLE_ASSISTANT",$roles)) {
            $assistantConnected = null;
            if (!empty($username))
                $assistantConnected = $assistantRepository->findOneByUsername($username);

            $promos = $promotionRepository->findAll();
            $promoOfAssistant = [];
            foreach ($promos as $promo) {
                if ($promo->getAssistant() === $assistantConnected) {
                    array_push($promoOfAssistant,$promo);
                }
            }

            $json = GenericSerializer::serializeJson($promoOfAssistant, ["groups" => "get_promotion"]);
            return new JsonResponse($json, Response::HTTP_OK);
        }
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
     * @param Formation|null $formation
     * @param ResponsableRepository $responsableRepository
     * @Security("is_granted('ROLE_RESPO')")
     */
    public function getPromotionsByFormation(ResponsableRepository $responsableRepository, Formation $formation = null):JsonResponse
    {
        $user = $this->getUser();
        if($user != null){
            $username = $user->getUsername();
            if(!empty($username))
                $responsableConnected = $responsableRepository->findOneByUsername($username);
        }



        if (is_null($formation)) {
            return new JsonResponse("Formation non  trouvée", Response::HTTP_CONFLICT);
        }

        $responsableCible = $formation->getResponsable();

        if ($responsableConnected === $responsableCible) {
            $promotions = $formation->getPromotions();

            $json = GenericSerializer::serializeJson($promotions, ["groups"=>"get_promotion"]);

            return new JsonResponse($json, Response::HTTP_OK);
        }
        else {
            return new JsonResponse("Vous ne pouvez obtenir des promotions dont vous n'etes pas responsable", Response::HTTP_FORBIDDEN);
        }
    }

    /**
     * @OA\Get(
     *      tags={"Promotions"},
     *      path="/responsables/promotions",
     *      @OA\Response(
     *          response="200",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/Promotion")
     *          )
     *      )
     * )
     * @Route("/responsables/promotions", name="promotions_by_responsable", methods={"GET"})
     * @param ResponsableRepository $personneRepository
     * @return Response
     * @Security("is_granted('ROLE_RESPO')")
     */
    public function getPromotionsForRespo(ResponsableRepository $personneRepository):Response
    {
        $promotions = [];
        $user = $this->getUser();
        if($user != null){
            $username = $user->getUsername();
            if(!empty($username)){
                $respo = $personneRepository->findOneByUsername($username);
                if($respo == null)
                    return new JsonResponse("Erreur...", Response::HTTP_NOT_FOUND);
                $formations = $respo->getFormations();
                foreach ($formations as $formation)
                {
                    foreach($formation->getPromotions() as $promotion)
                    {
                        array_push($promotions, $promotion);
                    }
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
     *              @OA\Property(type="string", property="nom", required = true)
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
     * @param ResponsableRepository $responsableRepository
     * @return JsonResponse
     * @Security("is_granted('ROLE_RESPO')")
     */
    public function AddPromotion(FormationRepository $formationRepository, PromotionRepository $promotionRepository, AssistantRepository $assistantRepository, Request $request, EntityManagerInterface $entityManager, ResponsableRepository $responsableRepository): Response
    {
        $user = $this->getUser();
        if($user != null){
            $username = $user->getUsername();
            if(!empty($username))
                $responsableConnected = $responsableRepository->findOneByUsername($username);
        }

        $repoResponse = $promotionRepository->AjoutPromotion($entityManager, $formationRepository, $promotionRepository, $assistantRepository, $request, $responsableConnected);

        switch ($repoResponse["status"]) {
            case 404:
                return new JsonResponse($repoResponse["error"], Response::HTTP_NOT_FOUND);
                break;
            case 201:
                $json = GenericSerializer::serializeJson($repoResponse["data"], ["groups" => "add_promotion"]);
                return new JsonResponse($json, Response::HTTP_CREATED);
                break;
            case 403:
                return new JsonResponse($repoResponse["error"], Response::HTTP_FORBIDDEN);
                break;
            default:
                return new JsonResponse($repoResponse["error"], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * @OA\Put(
     *      tags={"Promotions"},
     *      path="/promotion/{id}",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          @OA\JsonContent(type="object",
     *              @OA\Property(property="nom",type="string"),
     *              @OA\Property(property="idFormation",type="integer"),
     *              @OA\Property(property="idAssistant",type="integer")
     *          )
     *      ),
     *      @OA\Response(response="201", description="promotion modifiée !"),
     *      @OA\Response(response="409", description="La formation renseignée n'existe pas"),
     *      @OA\Response(response="404", description="Non trouvée...")
     * )
     * @Route("/promotion/{id}", name="update_promotion", methods={"PUT"})
     * @param PromotionRepository $promotionRepository
     * @param FormationRepository $formationRepository
     * @param AssistantRepository $assistantRepository
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param Promotion $promotion
     * @param ResponsableRepository $responsableRepository
     * @return JsonResponse
     * @Security("is_granted('ROLE_RESPO')")
     */
    public function UpdatePromotion(PromotionRepository $promotionRepository, FormationRepository $formationRepository, AssistantRepository $assistantRepository, Request $request, EntityManagerInterface $entityManager, Promotion $promotion, ResponsableRepository $responsableRepository): JsonResponse
    {

        $user = $this->getUser();
        if($user != null){
            $username = $user->getUsername();
            if(!empty($username))
                $responsableConnected = $responsableRepository->findOneByUsername($username);
        }

        $data = json_decode($request->getContent(), true);

        $nom = $data["nom"];
        $formation_id = $data["idFormation"];
        $assistant_id = $data["idAssistant"];

        if (empty($nom) || empty($formation_id) || empty($assistant_id)) {
            throw new NotFoundHttpException('Expecting mandatory parameters!');
        }

        $repoResponse = $promotionRepository->updatePromotion($entityManager, $formationRepository, $assistantRepository, $nom, $formation_id, $assistant_id, $promotion, $responsableConnected);

        switch ($repoResponse["status"]){
            case 201:
                $json = GenericSerializer::serializeJson($repoResponse['data'], ['groups'=>'update_promotion']);
                return new JsonResponse($json,Response::HTTP_CREATED);
                break;
            case 409:
                return new JsonResponse($repoResponse["error"],Response::HTTP_CONFLICT);
                break;
            case 403:
                return new JsonResponse($repoResponse["error"],Response::HTTP_FORBIDDEN);
                break;
            case 500:
                return new JsonResponse($repoResponse["error"],Response::HTTP_INTERNAL_SERVER_ERROR);
                break;
            default:
                return new JsonResponse(Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * @OA\Delete(
     *      tags={"Promotions"},
     *      path="/promotion/{id}",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(response="204", description="Promotion supprimée !"),
     *      @OA\Response(response="409", description="La promotion comporte des étudiants, suppression impossible"),
     *      @OA\Response(response="409", description="La promotion comporte des semestres, suppression impossible"),
     * )
     * @Route("/promotion/{id}", name="delete_promotion", methods={"DELETE"})
     * @param EntityManagerInterface $entityManager
     * @param ResponsableRepository $responsableRepository
     * @param PromotionRepository $promotionRepository
     * @param Promotion $promotion
     * @return Response
     * @Security("is_granted('ROLE_RESPO')")
     */
    public function deletePromotion(EntityManagerInterface $entityManager,ResponsableRepository $responsableRepository, PromotionRepository $promotionRepository,Promotion $promotion): Response
    {
        $user = $this->getUser();
        if($user != null){
            $username = $user->getUsername();
            if(!empty($username))
                $responsableConnected = $responsableRepository->findOneByUsername($username);
        }


        $repoResponse = $promotionRepository->deletePromotion($entityManager,$promotion,$responsableConnected);

        switch ($repoResponse["status"]){
            case 204:
                $json = GenericSerializer::serializeJson($promotion, ['groups'=>'delete_promotion']);
                return new JsonResponse($json,Response::HTTP_NO_CONTENT);
                break;
            case 409:
                return new JsonResponse($repoResponse["error"],Response::HTTP_CONFLICT);
                break;
            case 403:
                return new JsonResponse($repoResponse["error"],Response::HTTP_FORBIDDEN);
                break;
            case 500:
                return new JsonResponse($repoResponse["error"],Response::HTTP_INTERNAL_SERVER_ERROR);
                break;
            default:
                return new JsonResponse(Response::HTTP_NOT_FOUND);
        }
    }
}
