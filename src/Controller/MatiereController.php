<?php

namespace App\Controller;


use App\Entity\Matiere;
use App\Entity\Semestre;
use App\Repository\MatiereRepository;
use App\Repository\ModuleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class MatiereController extends AbstractController
{
    /**
     * @Route("/matieres", name="matiere_list", methods={"GET"})
     * @param MatiereRepository $matiereRepository
     * @return Response
     */
    public function list(MatiereRepository $matiereRepository): Response
    {
        $matieres = $matiereRepository->findAll();
        $matiereArray = [];


        foreach($matieres as $matiere){
            array_push($matiereArray, $matiere->getArray());
        }

        return new JsonResponse($matiereArray, Response::HTTP_OK);

    }

    /**
     * @Route("/matieres/{id}", name="matiere")
     * @param Matiere $matiere
     * @return Response
     */
    public function read(Matiere $matiere): Response
    {
        return new JsonResponse($matiere->getArray(), Response::HTTP_OK);

    }

    /**
     * @Route("/matieres", name="add_matiere", methods={"POST"})
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param ModuleRepository $moduleRepository
     * @return JsonResponse
     */
    public function add(Request $request, EntityManagerInterface $entityManager, ModuleRepository $moduleRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $nom = $data['nom'];
        $coeff = $data['coefficient'];
        $idModule = $data['idModule'];

        if (empty($nom) || empty($coeff) || empty($idModule)) {
            throw new NotFoundHttpException('Expecting mandatory parameters!');
        }

        $module = $moduleRepository->find($idModule);

        $matiere = new Matiere();
        $matiere->setNom($nom);
        $matiere->setCoefficient($coeff);
        $matiere->setModule($module);


        $entityManager->persist($matiere);
        $entityManager->flush();

        return new JsonResponse(['status' => 'Matière ajoutée !'], Response::HTTP_CREATED);

    }


    /**
     * @Route("/semestres/{id}/matieres", name="semestre_matieres", methods={"GET"})
     * @param Semestre $semestre
     * @return Response
     */
    public function getMatieresParSemestre(Semestre $semestre):Response
    {
        $modules = $semestre->getModules();
        $matieres = [];
        foreach ($modules as $module) {
            $mats = $module->getMatieres();
            foreach ($mats as $mat) {
                array_push($matieres, $mat->getArray());
            }
        }

        return new JsonResponse($matieres, Response::HTTP_OK);

    }
}
