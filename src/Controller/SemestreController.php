<?php

namespace App\Controller;

use App\Entity\Matiere;
use App\Entity\Promotion;
use App\Entity\Semestre;
use App\Entity\Session;
use App\Repository\SemestreRepository;
use App\Serializers\SessionSerializer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
     *          @OA\JsonContent(type="array",
     *               @OA\Items(ref="#/components/schemas/Semestre"))
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

    /**
     * @OA\Post(
     *      tags={"Semestres"},
     *      path="/promotions/{id}/semestre",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="id Promotion",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          @OA\JsonContent(type="object",
     *              @OA\Property(property="nom",type="string")
     *          )
     *      ),
     *      @OA\Response(response="201", description="Semestre ajouté !"),
     *      @OA\Response(response="404", description="Non trouvée...")
     * )
     * @Route("/promotions/{id}/semestre", name="add_semestre_by_promotion", methods={"POST"})
     * @param SemestreRepository $semestreRepository
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param Promotion $promotion
     * @return JsonResponse
     */
    public function add(SemestreRepository $semestreRepository, Request $request, EntityManagerInterface $entityManager, Promotion $promotion): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $nom = $data['nom'];

        if (empty($nom)) {
            throw new NotFoundHttpException('Expecting mandatory parameters!');
        }

        $repoResponse = $semestreRepository->add($entityManager,$promotion,$nom);



        $json = SessionSerializer::serializeJson($repoResponse["data"], ['groups'=>'add_semestre_by_promotion']);
        return new JsonResponse($json, Response::HTTP_CREATED);

    }

    /**
     * @OA\Put(
     *      tags={"Semestres"},
     *      path="/semestre/{id}",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="id Semestre",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          @OA\JsonContent(type="object",
     *              @OA\Property(property="nom",type="string")
     *          )
     *      ),
     *      @OA\Response(response="201", description="Semestre modifié !"),
     *      @OA\Response(response="404", description="Non trouvée...")
     * )
     * @Route("/semestre/{id}", name="update_semestre", methods={"PUT"})
     * @param SemestreRepository $semestreRepository
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param Promotion $promotion
     * @return JsonResponse
     */
    public function updateSemestre(SemestreRepository $semestreRepository, Request $request, EntityManagerInterface $entityManager, Promotion $promotion): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $nom = $data['nom'];

        if (empty($nom)) {
            throw new NotFoundHttpException('Expecting mandatory parameters!');
        }

        $repoResponse = $semestreRepository->add($entityManager,$promotion,$nom);

        $json = SessionSerializer::serializeJson($repoResponse["data"], ['groups'=>'add_semestre_by_promotion']);
        return new JsonResponse($json, Response::HTTP_CREATED);
    }

}
