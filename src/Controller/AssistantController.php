<?php

namespace App\Controller;

use App\Entity\Assistant;
use App\Repository\AssistantRepository;
use App\Serializers\AssistantSerializer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

class AssistantController extends AbstractController
{
    /**
     * @OA\Get(
     *      tags={"Assistants"},
     *      path="/assistants",
     *      @OA\Response(
     *          response="200",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/Assistant")
     *          )
     *      )
     * )
     * @Route("/assistants", name="assistant_list")
     * @param AssistantRepository $assistantRepository
     * @return Response
     */
    public function list(AssistantRepository $assistantRepository): Response
    {
        $assistants = $assistantRepository->findAll();
        /* $assistantsArray = [];


        foreach($assistants as $assistant){
            array_push($assistantsArray, $assistant->getArray());
        }

        return new JsonResponse($assistantsArray, Response::HTTP_OK);*/
        $json = AssistantSerializer::serializeJson($assistants, ["groups"=>"get_assistant"]);
        return new JsonResponse($json, Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *      tags={"Assistants"},
     *      path="/assistants/{id}",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response="200",
     *          @OA\JsonContent(ref="#/components/schemas/Assistant")
     *      )
     * )
     * @Route("/assistants/{id}", name="assistant")
     * @param Assistant $assistant
     * @return Response
     */
    public function read(Assistant $assistant): Response
    {
        //return new JsonResponse($assistant->getArray(), Response::HTTP_OK);
        $json = AssistantSerializer::serializeJson($assistant, ["groups"=>"get_assistant"]);
        return new JsonResponse($json, Response::HTTP_OK);
    }

}
