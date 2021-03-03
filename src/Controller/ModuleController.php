<?php

namespace App\Controller;

use App\Entity\Module;
use App\Entity\Promotion;
use App\Entity\Semestre;
use App\Repository\ModuleRepository;
use App\Repository\PromotionRepository;
use App\Repository\ResponsableRepository;
use App\Repository\SemestreRepository;
use App\Serializers\GenericSerializer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class ModuleController extends AbstractController
{
    /**
     * @OA\Get(
     *      tags={"Modules"},
     *      path="/modules",
     *      @OA\Response(
     *          response="200",
     *          @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Module"))
     *      )
     * )
     * @Route("/modules", name="module_list", methods={"GET"})
     * @param ModuleRepository $moduleRepository
     * @return Response
     *
     */
    public function list(ModuleRepository $moduleRepository): Response
    {
        $modules = $moduleRepository->findAll();
        $moduleArray = [];


        foreach($modules as $module){
            array_push($moduleArray, $module->getArray());
        }

        return new JsonResponse($moduleArray, Response::HTTP_OK);

    }

    /**
     * @OA\Get(
     *      tags={"Modules"},
     *      path="/modules/{id}",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response="200",
     *          @OA\JsonContent(ref="#/components/schemas/Module")
     *      )
     * )
     * @Route("/modules/{id}", name="module", methods={"GET"})
     * @param Module $module
     * @param ResponsableRepository $responsableRepository
     * @return Response
     * @Security("is_granted('ROLE_RESPO')")
     */
    public function read(Module $module, ResponsableRepository $responsableRepository): Response
    {
        $user = $this->getUser();
        if($user != null){
            $username = $user->getUsername();
            if(!empty($username))
                $responsableConnected = $responsableRepository->findOneByUsername($username);
        }

        $responsableCible = $module->getSemestre()->getPromotion()->getFormation()->getResponsable();

        if ($responsableCible === $responsableConnected) {
            return new JsonResponse($module->getArray(), Response::HTTP_OK);
        }
        else {
            return new JsonResponse("Vous n'êtes pas responsable du semestre contenant ce module", Response::HTTP_FORBIDDEN);
        }

    }

    /**
     * @OA\Get(
     *      tags={"Modules"},
     *      path="/promotions/{id}/modules",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response="200"
     *      ),
     *     @OA\Response(
     *          response="404"
     *      )
     * )
     * @Route("/promotions/{id}/modules", name="get_modules_by_promotion", methods={"GET"})
     * @param PromotionRepository $promotionRepository
     * @param ResponsableRepository $responsableRepository
     * @param Promotion $promotion
     * @return JsonResponse
     * @Security("is_granted('ROLE_RESPO')")
     */
    public function getModulesByPromotion(PromotionRepository $promotionRepository, ResponsableRepository $responsableRepository, Promotion $promotion): JsonResponse
    {

        $user = $this->getUser();
        if($user != null){
            $username = $user->getUsername();
            if(!empty($username))
                $responsableConnected = $responsableRepository->findOneByUsername($username);
        }

        $repoResponse = $promotionRepository->getModulesByPromotion($promotion,$responsableConnected);

        switch ($repoResponse["status"]){
            case 200:
                $json = GenericSerializer::serializeJson($repoResponse["data"], ['groups'=>'get_modules_by_promotion']);
                return new JsonResponse($json,Response::HTTP_OK);
                break;
            case 403:
                return new JsonResponse($repoResponse["error"],Response::HTTP_FORBIDDEN);
                break;
            case 404:
                return new JsonResponse($repoResponse["error"],Response::HTTP_NOT_FOUND);
                break;
            default:
                return new JsonResponse(Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * @OA\Post(
     *      tags={"Modules"},
     *      path="/semestres/{id}/modules",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="id Semestre",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          request="matieres",
     *          @OA\JsonContent(ref="#/components/schemas/Module")
     *      ),
     *      @OA\Response(response="201", description="Matiere ajoutée !"),
     *      @OA\Response(response="404", description="Non trouvé...")
     * )
     * @Route("/semestres/{id}/modules", name="add_module", methods={"POST"})
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param ResponsableRepository $responsableRepository
     * @param SemestreRepository $semestreRepository
     * @param Semestre $semestre
     * @return Response
     * @Security("is_granted('ROLE_RESPO')")
     */
    public function insertModuleInSemestre(
        Request $request,
        EntityManagerInterface $entityManager,
        ResponsableRepository $responsableRepository,
        SemestreRepository $semestreRepository,
        Semestre $semestre): Response
    {
        $user = $this->getUser();
        if($user != null) {
            $username = $user->getUsername();
            if (!empty($username))
                $responsableConnected = $responsableRepository->findOneByUsername($username);
        }

        $data = json_decode($request->getContent(), true);

        $nom = $data['nom'];
        if(!array_key_exists("ects",$data) || empty($nom)) {
            throw new NotFoundHttpException('Expecting mandatory parameters!');
        }
        $ects = $data['ects'];

        $repoResponse = $semestreRepository->insertModuleInSemestre($entityManager,$nom,$semestre,$ects,$responsableConnected);
        //$semestre = $semestreRepository->find($idSemestre);


        switch ($repoResponse["status"]){
            case 201:
                $json = GenericSerializer::serializeJson($repoResponse["data"], ["groups"=>"module_get"]);
                return new JsonResponse($json,Response::HTTP_CREATED);
                break;
            case 403 :
                return new JsonResponse($repoResponse["error"],Response::HTTP_FORBIDDEN);
                break;
            case 409:
                return new JsonResponse($repoResponse["error"],Response::HTTP_CONFLICT);
                break;
            default:
                return new JsonResponse(Response::HTTP_NOT_FOUND);
        }


    }

    /**
     * @OA\Delete(
     *      tags={"Modules"},
     *      path="/modules/{id}",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="id Module",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(response="204", description="Module supprimé !"),
     *      @OA\Response(response="409", description="Le module comporte des matières")
     * )
     * @Route("/modules/{id}", name="delete_module", methods={"DELETE"})
     * @param EntityManagerInterface $entityManager
     * @param ResponsableRepository $responsableRepository
     * @param ModuleRepository $moduleRepository
     * @param Module $module
     * @return Response
     * @Security("is_granted('ROLE_RESPO')")
     */
    public function deleteModule(EntityManagerInterface $entityManager,ResponsableRepository $responsableRepository, ModuleRepository $moduleRepository,Module $module): Response
    {
        $user = $this->getUser();
        if($user != null){
            $username = $user->getUsername();
            if(!empty($username))
                $responsableConnected = $responsableRepository->findOneByUsername($username);
        }


        $repoResponse = $moduleRepository->deleteModule($entityManager,$module,$responsableConnected);

        switch ($repoResponse["status"]){
            case 204:
                $json = GenericSerializer::serializeJson($module, ['groups'=>'delete_module']);
                return new JsonResponse("Ok",Response::HTTP_NO_CONTENT);
                break;
            case 403 :
                return new JsonResponse($repoResponse["error"],Response::HTTP_FORBIDDEN);
                break;
            case 409:
                return new JsonResponse($repoResponse["error"],Response::HTTP_CONFLICT);
                break;
            default:
                return new JsonResponse(Response::HTTP_NOT_FOUND);
        }

    }

    /**
     * @OA\Put(
     *      tags={"Modules"},
     *      path="/module/{id}",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="id Module",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          @OA\JsonContent(type="object",
     *              @OA\Property(property="nom",type="string"),
     *              @OA\Property(property="ects",type="integer"),
     *              @OA\Property(property="semestre_id",type="integer")
     *          )
     *      ),
     *      @OA\Response(response="201", description="Module modifié !"),
     *      @OA\Response(response="409", description="Le semestre renseigné n'existe pas"),
     *      @OA\Response(response="404", description="Non trouvée...")
     * )
     * @Route("/module/{id}", name="update_module", methods={"PUT"})
     * @param SemestreRepository $semestreRepository
     * @param ResponsableRepository $responsableRepository
     * @param ModuleRepository $moduleRepository
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param Module $module
     * @return JsonResponse
     * @Security("is_granted('ROLE_RESPO')")
     */
    public function updateModule(SemestreRepository $semestreRepository, ResponsableRepository $responsableRepository, ModuleRepository $moduleRepository, Request $request, EntityManagerInterface $entityManager, Module $module): JsonResponse
    {
        $user = $this->getUser();
        if($user != null){
            $username = $user->getUsername();
            if(!empty($username))
                $responsableConnected = $responsableRepository->findOneByUsername($username);
        }


        $data = json_decode($request->getContent(), true);

        $nom = $data["nom"];
        $ects = $data["ects"];
        $semestre_id = $data["semestre_id"];


        if (empty($nom) || empty($ects) || empty($semestre_id)) {
            throw new NotFoundHttpException('Expecting mandatory parameters!');
        }

        $repoResponse = $moduleRepository->updateModule($entityManager,$semestreRepository,$nom,$ects,$semestre_id,$module, $responsableConnected);

        switch ($repoResponse["status"]){
            case 201:
                $json = GenericSerializer::serializeJson($repoResponse['data'], ['groups'=>'module_get']);
                return new JsonResponse($json,Response::HTTP_CREATED);
                break;
            case 403:
                return new JsonResponse($repoResponse["error"],Response::HTTP_FORBIDDEN);
                break;
            case 409:
                return new JsonResponse($repoResponse["error"],Response::HTTP_CONFLICT);
                break;
            case 500:
                return new JsonResponse($repoResponse["error"],Response::HTTP_INTERNAL_SERVER_ERROR);
                break;
            default:
                return new JsonResponse(Response::HTTP_NOT_FOUND);
        }
    }


}
