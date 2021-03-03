<?php

namespace App\Controller;

use App\Entity\Intervenant;
use App\Entity\Matiere;
use App\Entity\Module;
use App\Entity\Promotion;
use App\Entity\Semestre;
use App\Repository\MatiereRepository;
use App\Repository\ModuleRepository;
use App\Repository\ResponsableRepository;
use App\Serializers\GenericSerializer;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class MatiereController extends AbstractController
{
    /**
     * @OA\Get(
     *      tags={"Matieres"},
     *      path="/matieres",
     *      @OA\Response(
     *          response="200",
     *          description ="Matieres",
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

        $json = GenericSerializer::serializeJson($matieres, ['groups' => 'matiere_get']);

        return new JsonResponse($json, Response::HTTP_OK);

    }

    /**
     * @OA\Delete(
     *      tags={"Matieres"},
     *      path="/matieres/{id}",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description ="id Matiere",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response="202",
     *      ),
     *      @OA\Response(
     *          response="404",
     *      )
     * )
     * @Route("/matieres/{id}", name="delete_matiere", methods={"DELETE"})
     * @param EntityManagerInterface $entityManager
     * @param MatiereRepository $matiereRepository
     * @param Matiere|null $matiere
     * @return JsonResponse
     */
    public function deleteMatiereById(EntityManagerInterface $entityManager, MatiereRepository $matiereRepository, Matiere $matiere = null): JsonResponse
    {
        if($matiere == null){
            return new JsonResponse("Matiere inexistante !",Response::HTTP_NOT_FOUND);
        }
        if(sizeof($matiere->getNotes()) > 0)
        {
            return new JsonResponse("Veuillez supprimer les notes associées avant de supprimer la matière !", Response::HTTP_CONFLICT);
        }
        
        /*$repoResponse = $matiereRepository->deleteMatiereById($entityManager, $idMatiere);

        switch ($repoResponse["status"]) {
            case 202:
                return new JsonResponse("Ok", Response::HTTP_ACCEPTED);
                break;
            case 404:
                return new JsonResponse($repoResponse["error"], Response::HTTP_NOT_FOUND);
                break;
            default:
                return new JsonResponse(Response::HTTP_NOT_FOUND);
        }*/
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
     *          description ="Matiere",
     *          @OA\JsonContent(ref="#/components/schemas/Matiere")
     *      )
     * )
     * @Route("/matieres/{id}", name="matiere", methods={"GET"})
     * @param Matiere $matiere
     * @return Response
     */
    public function read(Matiere $matiere): Response
    {
        $json = GenericSerializer::serializeJson($matiere, ['groups' => 'matiere_get']);

        return new JsonResponse($json, Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *      tags={"Matieres"},
     *      path="/modules/{id}/matieres",
     *      @OA\Parameter(
     *          name="id",
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
     * @Route("/modules/{id}/matieres", name="add_matiere", methods={"POST"})
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param Module $module
     * @return JsonResponse
     */
    public function add(Request $request, EntityManagerInterface $entityManager, Module $module): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $nom = $data["nom"];
        $coeff = $data["coefficient"];
        $nbhp = $data["nombreHeuresAPlacer"];

        if (empty($nom) || empty($coeff) || empty($nbhp)) {
            throw new NotFoundHttpException('Expecting mandatory parameters!');
        }

        $matiere = new Matiere();
        $matiere->setNom($nom);
        $matiere->setCoefficient($coeff);
        $matiere->setModule($module);
        $matiere->setNombreHeuresAPlacer($nbhp);


        $entityManager->persist($matiere);
        $entityManager->flush();

        $json = GenericSerializer::serializeJson($matiere, ["groups" => "matiere_get"]);
        return new JsonResponse($json, Response::HTTP_CREATED);

    }


    /**
     * @OA\Post(
     *      tags={"Matieres"},
     *      path="/matieres/{id}/intervenants/{idIntervenant}",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="id Matière",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="idIntervenant",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(response="200", description="Intervenant ajouté !"),
     *      @OA\Response(response="404", description="Non trouvé...")
     * )
     * @Route("/matieres/{id}/intervenants/{idIntervenant}", name="add_intervenant_to_matiere", methods={"POST"})
     * @Entity("intervenant", expr="repository.find(idIntervenant)")
     * @param MatiereRepository $repository
     * @param Matiere|null $matiere
     * @param Intervenant|null $intervenant
     * @IsGranted("ROLE_ASSISTANT")
     * @return JsonResponse
     */
    public function addIntervenant(MatiereRepository $repository, Matiere $matiere = null, Intervenant $intervenant = null): JsonResponse
    {
        if ($matiere == null || $intervenant == null) {
            return new JsonResponse("Matière ou intervenant inexistant", Response::HTTP_NOT_MODIFIED);
        }

        $repository->addIntervenant($matiere, $intervenant);

        $json = GenericSerializer::serializeJson($matiere, ["groups" => "matiere_get"]);
        return new JsonResponse($json, Response::HTTP_OK);

    }

    /**
     * @OA\Put(
     *      tags={"Matieres"},
     *      path="/matiere/{id}",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          request="matiere",
     *          @OA\JsonContent(type="object",
     *              @OA\Property(property="nom",type="string"),
     *              @OA\Property(property="coefficient",type="integer"),
     *              @OA\Property(property="nombreHeuresAPlacer",type="integer"),
     *              @OA\Property(property="module_id",type="integer")
     *          )
     *      ),
     *      @OA\Response(response="201", description="Matiere modifiée !"),
     *      @OA\Response(response="409", description="Module non-existant"),
     *      @OA\Response(response="500", description="Erreur lors de la modification en base de données"),
     * )
     * @Route("/matiere/{id}", name="update_matiere", methods={"PUT"})
     * @param Request $request
     * @param MatiereRepository $matiereRepository
     * @param ModuleRepository $moduleRepository
     * @param EntityManagerInterface $entityManager
     * @param Matiere $matiere
     * @param ResponsableRepository $responsableRepository
     * @return JsonResponse
     * @Security("is_granted('ROLE_RESPO')")
     */
    public function updatematiere(Request $request, MatiereRepository $matiereRepository, ModuleRepository $moduleRepository, EntityManagerInterface $entityManager, Matiere $matiere, ResponsableRepository $responsableRepository): JsonResponse
    {
        $user = $this->getUser();
        if($user != null){
            $username = $user->getUsername();
            if(!empty($username))
                $responsableConnected = $responsableRepository->findOneByUsername($username);
        }

        $data = json_decode($request->getContent(), true);

        $nom = $data["nom"];
        $coeff = $data["coefficient"];
        $nbhp = $data["nombreHeuresAPlacer"];
        $module_id = $data["module_id"];

        if (empty($nom) || empty($coeff) || empty($nbhp) || empty($module_id)) {
            throw new NotFoundHttpException('Expecting mandatory parameters!');
        }

        $repoResponse = $matiereRepository->updateMatiere($entityManager, $moduleRepository, $matiere, $nom, $coeff, $nbhp, $module_id,$responsableConnected);

        switch ($repoResponse["status"]) {
            case 201:
                $json = GenericSerializer::serializeJson($repoResponse['data'], ['groups' => 'matiere_get']);
                return new JsonResponse($json, Response::HTTP_CREATED);
                break;
            case 409:
                return new JsonResponse($repoResponse["error"], Response::HTTP_CONFLICT);
                break;
            case 403:
                return new JsonResponse($repoResponse["error"], Response::HTTP_FORBIDDEN);
                break;
            case 500:
                return new JsonResponse($repoResponse["error"], Response::HTTP_INTERNAL_SERVER_ERROR);
                break;
            default:
                return new JsonResponse(Response::HTTP_NOT_FOUND);
        }

        //$json = GenericSerializer::serializeJson($matiere, ["groups" => "post_matiere_in_module"]);
        //return new JsonResponse($json, Response::HTTP_CREATED);

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
     *          description ="Matieres",
     *          @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Matiere"))
     *      )
     * )
     * @Route("/semestres/{id}/matieres", name="semestre_matieres", methods={"GET"})
     * @param Semestre $semestre
     * @return Response
     */
    public function getMatieresParSemestre(Semestre $semestre): Response
    {
        $modules = $semestre->getModules();
        $matieres = [];
        foreach ($modules as $module) {
            $mats = $module->getMatieres();
            foreach ($mats as $mat) {
                array_push($matieres, $mat);
            }
        }

        $json = GenericSerializer::serializeJson($matieres, ['groups' => 'matiere_get']);

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
