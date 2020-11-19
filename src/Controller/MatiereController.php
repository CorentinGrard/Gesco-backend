<?php

namespace App\Controller;


use App\Entity\Matiere;
use App\Repository\MatiereRepository;
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

        return $this->json(
            $matiereArray
        );
    }

    /**
     * @Route("/matieres/{id}", name="matiere")
     * @param Matiere $matiere
     * @return Response
     */
    public function read(Matiere $matiere): Response
    {
        return $this->json(
            $matiere->getArray()
        );
    }

    /**
     * @Route("/matieres", name="add_matiere", methods={"POST"})
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function add(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $nom = $data['nom'];
        $coeff = $data['coefficient'];
        //TODO $module = $data['idModule'];

        if (empty($nom) || empty($coeff) /*TODO || empty($module) */) {
            throw new NotFoundHttpException('Expecting mandatory parameters!');
        }

        $matiere = new Matiere();
        $matiere->setNom($nom);
        $matiere->setCoefficient($coeff);
        //TODO $matiere->setModule($module);


        $entityManager->persist($matiere);
        $entityManager->flush();

        return new JsonResponse(['status' => 'Matière ajoutée !'], Response::HTTP_CREATED);

    }
}
