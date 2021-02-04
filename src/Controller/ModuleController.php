<?php

namespace App\Controller;

use App\Entity\Module;
use App\Repository\ModuleRepository;
use App\Repository\SemestreRepository;
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
     *      path="/modules",
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
     *      path="/modules/{idSemestre}",
     *      @OA\Parameter(
     *          name="idSemestre",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          request="matieres",
     *          @OA\JsonContent(ref="#/components/schemas/Module")
     *      ),
     *      @OA\Response(response="201", description="Matiere ajoutée !"),
     *      @OA\Response(response="404", description="Non trouvé...")
     * )
     * @Route("/modules/{idSemestre}", name="add_module", methods={"POST"})
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param SemestreRepository $semestreRepository
     * @param int $idSemestre
     * @return Response
     */
    public function add(
        Request $request,
        EntityManagerInterface $entityManager,
        SemestreRepository $semestreRepository,
        int $idSemestre): Response
    {
        $data = json_decode($request->getContent(), true);

        $nom = $data['nom'];
        //$idSemestre = $data['idSemestre'];
        $ects = $data['ects'];

        if (empty($nom) || empty($idSemestre) || empty($ects) ) {
            throw new NotFoundHttpException('Expecting mandatory parameters!');
        }

        $semestre = $semestreRepository->find($idSemestre);

        $module = new Module();
        $module->setNom($nom);
        $module->setSemestre($semestre);
        $module->setEcts($ects);


        $entityManager->persist($module);
        $entityManager->flush();

        return new JsonResponse(['status' => 'Module ajouté !'], Response::HTTP_CREATED);
    }


}
