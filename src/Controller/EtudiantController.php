<?php

namespace App\Controller;

use App\Repository\EtudiantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EtudiantController extends AbstractController
{
    /**
     * @Route("/etudiant", name="etudiant")
     */
    public function index(): Response
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/EtudiantController.php',
        ]);
    }

    /**
     * @Route("/etudiants/{idEtudiant}/matieres/{idMatiere}/note/{note}", methods={"POST"}, requirements={"idEtudiant"="\d+", "idMatiere"="\d+"})
     * @param EtudiantRepository $etudiantRepository
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function ajoutNoteAEtudiantDansUneMatiere(EtudiantRepository $etudiantRepository, int $idEtudiant, int $idMatiere, Request $request, EntityManagerInterface $entityManager): Response
    {

        $data = json_decode($request->getContent(), true);
        $note = $data['note'];



        $etudiantRepository->ajoutNoteEtudiant($idEtudiant,$idMatiere,$note);

        return $this->json([

        ]);
    }
}
