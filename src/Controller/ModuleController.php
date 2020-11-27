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

class ModuleController extends AbstractController
{
    /**
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
     * @Route("/modules/{id}", name="module", methods={"GET"})
     * @param Module $module
     * @return Response
     */
    public function read(Module $module): Response
    {
        return new JsonResponse($module->getArray(), Response::HTTP_OK);
    }

    /**
     * @Route("/modules", name="add_module", methods={"POST"})
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param SemestreRepository $semestreRepository
     * @return Response
     */
    public function add(Request $request, EntityManagerInterface $entityManager, SemestreRepository $semestreRepository): Response
    {
        $data = json_decode($request->getContent(), true);

        $nom = $data['nom'];
        $idSemestre = $data['idSemestre'];
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
