<?php

namespace App\Controller;

use App\Entity\Promotion;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SemestreController extends AbstractController
{
    /**
     * @Route("/promos/{id}/semestres", name="promo_semestres")
     * @param Promotion $promotion
     * @return Response
     */
    public function list(Promotion $promotion): Response
    {
        $semestres = $promotion->getSemestres();
        $semestreArray = [];
        foreach($semestres as $semestre){
            array_push($semestreArray, $semestre->getArray());
        }

        return $this->json([
            'status' => 200,
            'result' => $semestreArray
        ]);
    }
}
