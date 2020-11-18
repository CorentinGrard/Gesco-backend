<?php

namespace App\Controller;


use App\Entity\Matiere;
use App\Repository\MatiereRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MatiereController extends AbstractController
{
    /**
     * @Route("/matieres", name="matiere_list")
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
}
