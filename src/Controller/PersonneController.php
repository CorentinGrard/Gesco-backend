<?php

namespace App\Controller;

use App\Entity\Personne;
use App\Repository\PersonneRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PersonneController extends AbstractController
{
    /**
     * @Route("/personnes", name="personne")
     */
    public function list(PersonneRepository $personneRepository): Response
    {

        $personnes = $personneRepository->findAll();
        $personnesArray = [];


        foreach($personnes as $personne){
            array_push($personnesArray, $personne->getArray());
        }

        return $this->json(
            $personnesArray
        );
    }

    /**
     * @Route("/personnes/{id}", name="personne")
     */
    public function getPersonneById(Personne $personne) {

        return $this->json(
            $personne->getArray()
        );

    }


}
