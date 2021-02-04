<?php

namespace App\Controller;


use App\Entity\Matiere;
use App\Entity\Promotion;
use App\Entity\Semestre;
use App\Repository\MatiereRepository;
use App\Repository\ModuleRepository;
use App\Serializers\MatiereSerializer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

class MatiereController extends AbstractController
{
    /**
     * @OA\Get(
     *      tags={"Matieres"},
     *      path="/matieres",
     *      @OA\Response(
     *          response="200",
     *          @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Matiere"))
     *      )
     * )
     * @Route("/matieres", name="matiere_list", methods={"GET"})
     * @param MatiereRepository $matiereRepository
     * @return Response
     */
    public function list(MatiereRepository $matiereRepository): Response
    {
        $matieres = $matiereRepository->findAll();

        $json = MatiereSerializer::serializeJson($matieres,['groups'=>'matiere_get']);

        return new JsonResponse($json, Response::HTTP_OK);

    }

    /**
     * @OA\Get(
     *      tags={"Matieres"},
     *      path="/matieres/{id}",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response="200",
     *          @OA\JsonContent(ref="#/components/schemas/Matiere")
     *      )
     * )
     * @Route("/matieres/{id}", name="matiere")
     * @param Matiere $matiere
     * @return Response
     */
    public function read(Matiere $matiere): Response
    {
        $json = MatiereSerializer::serializeJson($matiere, ['groups'=>'matiere_get']);

        return new JsonResponse($json, Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *      tags={"Matieres"},
     *      path="/matieres/{idModule}",
     *      @OA\Parameter(
     *          name="idModule",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          request="matieres",
     *          @OA\JsonContent(ref="#/components/schemas/Matiere")
     *      ),
     *      @OA\Response(response="201", description="Matiere ajoutée !"),
     *      @OA\Response(response="404", description="Non trouvé...")
     * )
     * @Route("/matieres/{idModule}", name="add_matiere", methods={"POST"})
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param ModuleRepository $moduleRepository
     * @param int $idModule
     * @return JsonResponse
     */
    public function add(Request $request, EntityManagerInterface $entityManager, ModuleRepository $moduleRepository, int $idModule): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $nom = $data['nom'];
        $coeff = $data['coefficient'];
        //$idModule = $data['idModule'];
        $nbhp = $data['nombreHeuresAPlacer'];

        if (empty($nom) || empty($coeff) || empty($idModule) || empty($idModule)) {
            throw new NotFoundHttpException('Expecting mandatory parameters!');
        }

        $module = $moduleRepository->find($idModule);

        $matiere = new Matiere();
        $matiere->setNom($nom);
        $matiere->setCoefficient($coeff);
        $matiere->setModule($module);
        $matiere->setNombreHeuresAPlacer($nbhp);


        $entityManager->persist($matiere);
        $entityManager->flush();

        return new JsonResponse(['status' => 'Matière ajoutée !'], Response::HTTP_CREATED);

    }


    /**
     * @OA\Get(
     *      tags={"Matieres"},
     *      path="/semestres/{id}/matieres",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response="200",
     *          @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Matiere"))
     *      )
     * )
     * @Route("/semestres/{id}/matieres", name="semestre_matieres", methods={"GET"})
     * @param Semestre $semestre
     * @return Response
     */
    public function getMatieresParSemestre(Semestre $semestre):Response
    {
        $modules = $semestre->getModules();
        $matieres = [];
        foreach ($modules as $module) {
            $mats = $module->getMatieres();
            foreach ($mats as $mat) {
                array_push($matieres, $mat);
            }
        }

        $json = MatiereSerializer::serializeJson($matieres,['groups'=>'matiere_get']);

        return new JsonResponse($json, Response::HTTP_OK);

    }

    /**
     * @OA\Get(
     *      tags={"Matieres"},
     *      path="/promotions/{id}/matieres",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response="200"
     *      )
     * )
     * @Route("/promotions/{id}/matieres", name="promotion_matieres", methods={"GET"})
     * @param MatiereRepository $matiereRepository
     * @param Promotion $promotion
     * @return Response
     */
    public function getMatieresParPromotions(MatiereRepository $matiereRepository, Promotion $promotion): Response
    {
        $matieres = $matiereRepository->getMatieresByPromotion($promotion->getId());
        return new JsonResponse($matieres, Response::HTTP_OK);
    }

}
