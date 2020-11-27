<?php

namespace App\Controller;

use App\Entity\Assistant;
use App\Entity\Promotion;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PromotionController extends AbstractController
{
    /**
     * @Route("/assistants/{id}/promos", name="promos_assistant", methods={"GET"})
     * @param Assistant $assistant
     */
    public function promosParAssistant(Assistant $assistant):Response
    {
        $promos = $assistant->getPromotions();

        $promoArray = [];

        foreach($promos as $promo){
            array_push($promoArray,$promo->getArray());
        }

        return new JsonResponse($promoArray, Response::HTTP_OK);

    }
}
