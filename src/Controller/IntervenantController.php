<?php

namespace App\Controller;

use App\Entity\Intervenant;
use App\Entity\Matiere;
use App\Repository\IntervenantRepository;
use App\Serializers\GenericSerializer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

class IntervenantController extends AbstractController
{
    /**
     * @OA\Get(
     *      tags={"Intervenants"},
     *      path="/intervenants",
     *      @OA\Response(
     *          response="200",
     *          @OA\JsonContent(ref="#/components/schemas/Intervenant")
     *      )
     * )
     * @Route("/intervenants", name="list_intervenants")
     * @param IntervenantRepository $intervenantRepository
     * @return Response
     */
    public function list(IntervenantRepository $intervenantRepository): Response
    {
        $intervenants = $intervenantRepository->findAll();

        $json = GenericSerializer::serializeJson($intervenants, ["groups"=>"get_intervenant"]);

        return new JsonResponse($json, Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *      tags={"Intervenants"},
     *      path="/intervenants/{id}",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response="200",
     *          @OA\JsonContent(ref="#/components/schemas/Intervenant")
     *      )
     * )
     * @Route("/intervenants/{id}", name="list_intervenants", methods={"GET"})
     * @param Intervenant|null $intervenant
     * @return Response
     */
    public function read(Intervenant $intervenant = null): Response
    {
        if($intervenant == null){
            return new JsonResponse("Non trouvé", Response::HTTP_NOT_FOUND);
        }

        $json = GenericSerializer::serializeJson($intervenant, ["groups"=>"get_intervenant"]);

        return new JsonResponse($json, Response::HTTP_OK);
    }


    /**
     * @OA\Get(
     *      tags={"Intervenants"},
     *      path="/matieres/{id}/intervenants",
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
     * @Route("/matieres/{id}/intervenants", name="intervenant", methods={"GET"})
     * @param Matiere|null $matiere
     * @return Response
     */
    public function intervenantsParMatiere(Matiere $matiere = null): Response
    {
        if($matiere == null){
            return new JsonResponse("Non trouvé", Response::HTTP_NOT_FOUND);
        }

        $json = GenericSerializer::serializeJson($matiere, ["groups"=>"get_intervenant_by_matiere"]);

        return new JsonResponse($json, Response::HTTP_OK);
    }

    /**
     * @OA\Delete(
     *      tags={"Intervenants"},
     *      path="/intervenants/{id}",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(response="200", description="Intervenant supprimé"),
     *      @OA\Response(response="404", description="Intervenant non supprimé"),
     * )
     * @Route("/intervenants/{id}", name="delete_intervenant", methods={"DELETE"})
     * @param Intervenant|null $intervenant
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function deleteIntervenant(EntityManagerInterface $em, Intervenant $intervenant = null):Response
    {
        if($intervenant == null){
            return new JsonResponse("Intervenant inexistant", Response::HTTP_NOT_FOUND);
        }
        $nom = $intervenant->getPersonne()->getPrenom() . " " .  $intervenant->getPersonne()->getNom();
        $em->remove($intervenant);
        $em->flush();

        return new JsonResponse("Intervenant '$nom' supprimé", Response::HTTP_OK);
    }


    /**
     * @OA\Post(
     *      tags={"Intervenants"},
     *      path="/intervenants",
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              @OA\Property(property="prenom", ref="#/components/schemas/Personne/properties/prenom"),
     *              @OA\Property(property="nom", ref="#/components/schemas/Personne/properties/nom"),
     *              @OA\Property(property="email", ref="#/components/schemas/Personne/properties/email"),
     *              @OA\Property(property="adresse", ref="#/components/schemas/Personne/properties/adresse"),
     *              @OA\Property(property="tel", ref="#/components/schemas/Personne/properties/numeroTel")
     *          )
     *      ),
     *      @OA\Response(response="201", description="Intervenant ajouté !"),
     *      @OA\Response(response="409", description="Erreur lors de la création.")
     * )
     * @Route("/intervenants", methods={"POST"})
     * @param Request $request
     * @param IntervenantRepository $repository
     * @return Response
     */
    public function addIntervenant(Request $request, IntervenantRepository $repository):Response
    {
        $data = json_decode($request->getContent(), true);

        $nom = $data['nom'];
        $prenom = $data['prenom'];
        $email = $data['email'];

        $adresse = null;
        if(array_key_exists("adresse",$data))
                $adresse = $data['adresse'];
        $tel = null;
        if(array_key_exists("tel",$data))
            $tel = $data['tel'];

        if (empty($nom) || empty($prenom) || empty($email)) {
            throw new NotFoundHttpException('Paramètres obligatoires attendus : nom, prenom, email');
        }

        $intervenant = $repository->createIntervenant($nom, $prenom, $email, $adresse, $tel);

        if($intervenant == null){
            return new JsonResponse("Erreur lors de la création.", Response::HTTP_CONFLICT);
        }

        $json = GenericSerializer::serializeJson($intervenant, ["groups"=>"get_intervenant"]);
        return new JsonResponse($json, Response::HTTP_CREATED);

    }

}
