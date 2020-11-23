<?php

namespace App\Controller;

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



        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/PersonneController.php',
        ]);
    }
}
