<?php

namespace App\Controller;

use App\Entity\Intervenant;
use App\Entity\Matiere;
use App\Repository\IntervenantRepository;
use App\Serializers\GenericSerializer;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
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
     * @Route("/intervenants", name="list_intervenants", methods={"GET"})
     * @param IntervenantRepository $intervenantRepository
     * @return Response
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_ASSISTANT')")
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
     * @Route("/intervenants/{id}", name="get_intervenant", methods={"GET"})
     * @param Intervenant|null $intervenant
     * @return Response
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_ASSISTANT')")
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
     * @Route("/matieres/{id}/intervenants", name="intervenant_by_matiere", methods={"GET"})
     * @param Matiere|null $matiere
     * @return Response
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_ASSISTANT')")
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
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_ASSISTANT')")
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
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_ASSISTANT')")
     */
    public function addIntervenant(Request $request, IntervenantRepository $repository):Response
    {
        $data = json_decode($request->getContent(), true);

        $nom = $data['nom'];
        $prenom = $data['prenom'];
        $email = $data['email'];

        $adresse = null;
        if(array_key_exists("adresse",$data))
        {
            $adresse = $data['adresse'];
        }
        $tel = null;
        if(array_key_exists("tel",$data))
        {
            $tel = $data['tel'];
        }

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


    /**
     * @OA\Put(
     *      tags={"Intervenants"},
     *      path="/intervenants/{id}",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              @OA\Property(property="prenom", ref="#/components/schemas/Personne/properties/prenom"),
     *              @OA\Property(property="nom", ref="#/components/schemas/Personne/properties/nom"),
     *              @OA\Property(property="email", ref="#/components/schemas/Personne/properties/email"),
     *              @OA\Property(property="adresse", ref="#/components/schemas/Personne/properties/adresse"),
     *              @OA\Property(property="tel", ref="#/components/schemas/Personne/properties/numeroTel")
     *          )
     *      ),
     *      @OA\Response(response="201", description="Intervenant modifié !"),
     *      @OA\Response(response="304", description="Intervenant inexistant")
     * )
     * @Route("/intervenants/{id}", methods={"PUT"})
     * @param Request $request
     * @param IntervenantRepository $repository
     * @param Intervenant|null $intervenant
     * @return Response
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_ASSISTANT')")
     */
    public function updateIntervenant(Request $request, IntervenantRepository $repository, Intervenant $intervenant = null):Response
    {
        if($intervenant == null){
            return new JsonResponse("Intervenant inexistant", Response::HTTP_NOT_MODIFIED);
        }

        $data = json_decode($request->getContent(), true);

        $nom = $data['nom'];
        $prenom = $data['prenom'];
        $email = $data['email'];

        $adresse = null;
        if(array_key_exists("adresse",$data))
        {
            $adresse = $data['adresse'];
        }
        $tel = null;
        if(array_key_exists("tel",$data))
        {
            $tel = $data['tel'];
        }

        if (empty($nom) || empty($prenom) || empty($email)) {
            throw new NotFoundHttpException('Paramètres obligatoires attendus : nom, prenom, email');
        }

        $repository->updateIntervenant($intervenant, $nom, $prenom, $email, $adresse, $tel);

        $json = GenericSerializer::serializeJson($intervenant, ["groups"=>"get_intervenant"]);
        return new JsonResponse($json, Response::HTTP_OK);

    }

}
