<?php

namespace App\Controller;

use App\Entity\Assistant;
use App\Entity\Etudiant;
use App\Entity\Intervenant;
use App\Entity\Personne;
use App\Entity\Responsable;
use App\Repository\AssistantRepository;
use App\Repository\IntervenantRepository;
use App\Repository\PersonneRepository;
use App\Repository\ResponsableRepository;
use App\Serializers\GenericSerializer;
use App\Tools;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Annotations\Property;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

class AdminController extends AbstractController
{
    /**
     * @OA\Post(
     *      tags={"Admin"},
     *      path="/admin/personnes/{id}/roles",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              @Property(property="role", type="string")
     *          )
     *      ),
     *     @OA\Response(response="201", description="Personne modifiée !"),
     * )
     * @Route("/admin/personnes/{id}/roles", name="add_role", methods={"POST"})
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param Personne|null $personne
     * @return JsonResponse
     */
    public function addRole(Request $request, EntityManagerInterface $em, Personne $personne = null)
    {
        if ($personne == null) {
            return new JsonResponse("La personne n'existe pas", Response::HTTP_NOT_FOUND);
        }
        $data = json_decode($request->getContent(), true);

        $role = $data["role"];

        switch ($role) {
            case "ROLE_ADMIN" :
                $personne->addRole($role);
                break;
            case "ROLE_ASSISTANT" :
                $assistant = new Assistant();
                $assistant->setPersonne($personne);
                $em->persist($assistant);
                break;
            case "ROLE_RESPO" :
                $respo = new Responsable();
                $respo->setPersonne($personne);
                $em->persist($respo);
                break;
            case "ROLE_INTER" :
                $inter = new Intervenant();
                $inter->setPersonne($personne);
                $em->persist($inter);
                break;
            /*            case "ROLE_ETUDIANT" :
                            $etudiant = new Etudiant();
                            $etudiant->setPersonne($personne);
                            $em->persist($etudiant);
                            break;*/
        }

        $em->persist($personne);

        $em->flush();

        $json = GenericSerializer::serializeJson($personne, ["groups" => "get_personne"]);

        return new JsonResponse($json, Response::HTTP_CREATED);

    }


    /**
     * @OA\Delete(
     *      tags={"Admin"},
     *      path="/admin/personnes/{id}/roles",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              @Property(property="role", type="string")
     *          )
     *      ),
     *     @OA\Response(response="201", description="Personne modifiée !"),
     * )
     * @Route("/admin/personnes/{id}/roles", name="remove_role", methods={"DELETE"})
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param Personne|null $personne
     * @param AssistantRepository $assistantRepository
     * @param ResponsableRepository $responsableRepository
     * @param IntervenantRepository $intervenantRepository
     * @return JsonResponse
     */
    public function removeRole(Request $request, EntityManagerInterface $em, Personne $personne = null,
                               AssistantRepository $assistantRepository,
                               ResponsableRepository $responsableRepository,
                               IntervenantRepository $intervenantRepository)
    {
        if ($personne == null) {
            return new JsonResponse("La personne n'existe pas", Response::HTTP_NOT_FOUND);
        }
        $data = json_decode($request->getContent(), true);

        $role = $data["role"];
        if ($role == "ROLE_USER") {
            return new JsonResponse("ROLE_USER ne peut être supprimé", Response::HTTP_NOT_MODIFIED);
        }


        switch ($role) {
            case "ROLE_ADMIN" :
                $personne->removeRole($role);
                break;
            case "ROLE_ASSISTANT" :
                $assistant = $assistantRepository->findOneByUsername($personne->getEmail());
                $em->remove($assistant);
                break;
            case "ROLE_RESPO" :
                $respo = $responsableRepository->findOneByUsername($personne->getEmail());
                $em->remove($respo);
                break;
            case "ROLE_INTER" :
                $inter = $intervenantRepository->findOneByUsername($personne->getEmail());
                $em->remove($inter);
                break;
            /*            case "ROLE_ETUDIANT" :
                            $etudiant = new Etudiant();
                            $etudiant->setPersonne($personne);
                            $em->persist($etudiant);
                            break;*/
        }

        $em->persist($personne);
        $em->flush();

        $json = GenericSerializer::serializeJson($personne, ["groups" => "get_personne"]);

        return new JsonResponse($json, Response::HTTP_CREATED);

    }

    /**
     * @OA\Get(
     *     tags={"Admin"},
     *     path="/admin/eligible-responsable/personnes",
     *      @OA\Response(
     *          response="200",
     *          @OA\JsonContent(ref="#/components/schemas/Personne")
     *      )
     * )
     * @Route("/admin/eligible-responsable/personnes", name="get_eligible_respo", methods={"GET"})
     * @param PersonneRepository $personneRepository
     * @param AssistantRepository $assistantRepository
     * @param IntervenantRepository $intervenantRepository
     * @param ResponsableRepository $responsableRepository
     * @return JsonResponse
     */
    public function getPersonneEligibleRespo(PersonneRepository $personneRepository, AssistantRepository $assistantRepository, IntervenantRepository $intervenantRepository, ResponsableRepository $responsableRepository): JsonResponse
    {
        $personnes = $personneRepository->findAll();
        $intervenants = $intervenantRepository->findAll();
        $assistants = $assistantRepository->findAll();
        $responsables = $responsableRepository->findAll();

        $personneArray = [];
        foreach ($personnes as $personne) {
            if ($personne->hasRole("ROLE_ADMIN")) {
                array_push($personneArray, $personne);
            }
        }

        foreach ($assistants as $assistant) {
            $personne = $assistant->getPersonne();
            if(!Tools::personneAlreadyInArray($personneArray, $personne->getId()))
                array_push($personneArray, $personne);
        }

        foreach ($intervenants as $intervenant) {
            if (!$intervenant->getExterne()) {
                $personne = $intervenant->getPersonne();
                if(!Tools::personneAlreadyInArray($personneArray, $personne->getId()))
                    array_push($personneArray, $personne);

            }
        }
        foreach ($responsables as $responsable) {
            $personne = $responsable->getPersonne();
            if(!Tools::personneAlreadyInArray($personneArray, $personne->getId()))
                array_push($personneArray, $personne);

        }

        $json = GenericSerializer::serializeJson($personneArray, ["groups" => "get_personne"]);
        return new JsonResponse($json, Response::HTTP_OK);
    }


    /**
     * @OA\Get(
     *      tags={"Admin"},
     *      path="/admin/roles",
     *      @OA\Response(response="200", description="Roles"),
     * )
     * @Route("/admin/roles", name="get_roles", methods={"GET"})
     */
    public function listRole()
    {
        $roles = [
            "ROLE_ADMIN",
            "ROLE_ASSISTANT",
            "ROLE_RESPO",
            "ROLE_INTER",
            "ROLE_USER", # ALWAYS REQUIRED
            "ROLE_ETUDIANT",
        ];
        return new JsonResponse($roles, Response::HTTP_OK);
    }

}
