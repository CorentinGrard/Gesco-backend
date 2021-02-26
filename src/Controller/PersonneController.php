<?php

namespace App\Controller;

use App\Entity\Assistant;
use App\Entity\Personne;
use App\Repository\AssistantRepository;
use App\Repository\IntervenantRepository;
use App\Repository\PersonneRepository;
use App\Repository\ResponsableRepository;
use App\Serializers\GenericSerializer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

class PersonneController extends AbstractController
{
    /**
     * @OA\Get(
     *      tags={"Personnes"},
     *      path="/personnes",
     *      @OA\Response(
     *          response="200",
     *          @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Personne"))
     *      )
     * )
     * @Route("/personnes", name="personne_list")
     * @param PersonneRepository $personneRepository
     * @return Response
     */
    public function list(PersonneRepository $personneRepository): Response
    {

        $personnes = $personneRepository->findAll();

        $json = GenericSerializer::serializeJson($personnes, ['groups' => 'get_personne']);

        return new JsonResponse($json, Response::HTTP_OK);

    }

    /**
     * @OA\Get(
     *      tags={"Personnes"},
     *      path="/personnes/{id}",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response="200",
     *          @OA\JsonContent(ref="#/components/schemas/Personne")
     *      )
     * )
     * @Route("/personnes/{id}", name="personne")
     * @param Personne $personne
     * @return Response
     */
    public function getPersonneById(Personne $personne): Response
    {

        $json = GenericSerializer::serializeJson($personne, ['groups' => 'get_personne']);

        return new JsonResponse($json, Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *      tags={"Personnes"},
     *      path="/profil",
     *      @OA\Response(
     *          response="200",
     *          @OA\JsonContent(ref="#/components/schemas/Personne")
     *      )
     * )
     * @Route("/profil", name="profil", methods={"GET"})
     * @param PersonneRepository $personneRepository
     * @return Response
     * @Security("is_granted('ROLE_USER')")
     */
    public function profil(PersonneRepository $personneRepository): Response
    {
        //$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();
        if ($user != null) {
            $username = $user->getUsername();
            if (!empty($username)) {
                $personne = $personneRepository->findOneByUsername($username);
                $json = GenericSerializer::serializeJson($personne, ['groups' => 'get_personne']);
                return new JsonResponse($json, Response::HTTP_OK);
            }
        }

        return new JsonResponse("Non trouvé", Response::HTTP_NOT_FOUND);


    }

    /**
     * @OA\Get(
     *      tags={"Personnes"},
     *      path="/personnes/eligiblent/intervenants",
     *      @OA\Response(
     *          response="200",
     *          @OA\JsonContent(ref="#/components/schemas/Personne")
     *      )
     * )
     * @Route("/personnes/eligiblent/intervenants", name="get_personnes_eligibles_responsable", methods={ "GET" } )
     * @param PersonneRepository $personneRepository
     * @param AssistantRepository $assistantRepository
     * @param IntervenantRepository $intervenantRepository
     * @param ResponsableRepository $responsableRepository
     * @return Response
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function getPersonnesEligiblentIntervenants(PersonneRepository $personneRepository, AssistantRepository $assistantRepository, IntervenantRepository $intervenantRepository, ResponsableRepository $responsableRepository): Response
    {

        $repoResponse = $personneRepository->getPersonnesEligiblentIntervenants($personneRepository, $assistantRepository, $responsableRepository, $intervenantRepository);

        switch ($repoResponse["status"]) {
            case 200:
                $json = GenericSerializer::serializeJson($repoResponse['data'], ['groups' => 'get_personne']);
                return new JsonResponse($json, Response::HTTP_OK);
                break;
            case 500:
                return new JsonResponse($repoResponse["error"], Response::HTTP_INTERNAL_SERVER_ERROR);
                break;
            default:
                return new JsonResponse(Response::HTTP_NOT_FOUND);
        }
    }

}
