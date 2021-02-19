<?php

namespace App\Controller;

use App\Entity\Module;
use App\Entity\Semestre;
use App\Repository\ModuleRepository;
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
     * @return Response
     */
    public function read(Module $module): Response
    {
        return new JsonResponse($module->getArray(), Response::HTTP_OK);
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
     * @param SemestreRepository $semestreRepository
     * @param Semestre $semestre
     * @return Response
     */
    public function add(
        Request $request,
        EntityManagerInterface $entityManager,
        SemestreRepository $semestreRepository,
        Semestre $semestre): Response
    {
        $data = json_decode($request->getContent(), true);

        $nom = $data['nom'];
        //$idSemestre = $data['idSemestre'];
        $ects = $data['ects'];

        if (empty($nom) /*|| empty($ects)*/ ) {
            throw new NotFoundHttpException('Expecting mandatory parameters!');
        }

        //$semestre = $semestreRepository->find($idSemestre);

        $module = new Module();
        $module->setNom($nom);
        $module->setSemestre($semestre);
        $module->setEcts($ects);


        $entityManager->persist($module);
        $entityManager->flush();

        return new JsonResponse(['status' => 'Module ajouté !'], Response::HTTP_CREATED);
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
     * @param ModuleRepository $moduleRepository
     * @param Module $module
     * @return Response
     */
    public function deleteModule(EntityManagerInterface $entityManager,ModuleRepository $moduleRepository,Module $module): Response
    {

        $repoResponse = $moduleRepository->deleteModule($entityManager,$module);

        switch ($repoResponse["status"]){
            case 204:
                $json = GenericSerializer::serializeJson($module, ['groups'=>'delete_module']);
                return new JsonResponse("Ok",Response::HTTP_NO_CONTENT);
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
     * @param ModuleRepository $moduleRepository
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param Module $module
     * @return JsonResponse
     */
    public function updateModule(SemestreRepository $semestreRepository,ModuleRepository $moduleRepository, Request $request, EntityManagerInterface $entityManager, Module $module): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $nom = $data["nom"];
        $ects = $data["ects"];
        $semestre_id = $data["semestre_id"];


        if (empty($nom) || empty($ects) || empty($semestre_id)) {
            throw new NotFoundHttpException('Expecting mandatory parameters!');
        }

        $repoResponse = $moduleRepository->updateModule($entityManager,$semestreRepository,$nom,$ects,$semestre_id,$module);

        switch ($repoResponse["status"]){
            case 201:
                $json = GenericSerializer::serializeJson($repoResponse['data'], ['groups'=>'update_module']);
                return new JsonResponse($json,Response::HTTP_CREATED);
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
