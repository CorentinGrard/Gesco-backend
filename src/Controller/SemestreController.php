<?php

namespace App\Controller;

use App\Entity\Promotion;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

class SemestreController extends AbstractController
{
    /**
     * @OA\Get(
     *      tags={"Semestres"},
     *      path="/promotions/{id}/semestres",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="id Promotion",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response="200",
     *          @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Semestre"))
     *      )
     * )
     * @Route("/promotions/{id}/semestres", name="promo_semestres")
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

        return new JsonResponse($semestreArray, Response::HTTP_OK);

        /*return $this->json([
            'status' => 200,
            'result' => $semestreArray
        ]);*/
    }
}
