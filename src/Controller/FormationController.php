<?php

namespace App\Controller;

use App\Entity\Formation;
use App\Entity\Responsable;
use App\Repository\FormationRepository;
use App\Repository\IntervenantRepository;
use App\Repository\PersonneRepository;
use App\Repository\ResponsableRepository;
use App\Serializers\GenericSerializer;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

class FormationController extends AbstractController
{
    /**
     * @OA\Get(
     *      tags={"Formations"},
     *      path="/formations/{id}",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="id Formation",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response="200",
     *      ),
     *      @OA\Response(
     *          response="404",
     *          description="Formation inexistante !",
     *      )
     * )
     * @Route("/formations/{id}", name="get_formation", methods={"GET"})
     * @param Formation|null $formation
     * @return JsonResponse
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function index(Formation $formation = null): JsonResponse
    {
        if ($formation == null)
            return new JsonResponse("Formation inexistante !", Response::HTTP_NOT_FOUND);
        return new JsonResponse(GenericSerializer::serializeJson($formation, ["groups" => "get_formation"]), Response::HTTP_OK);
    }

    /**
     * @OA\Delete(
     *      tags={"Formations"},
     *      path="/formations/{id}",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description ="id Formation",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response="202",
     *      ),
     *      @OA\Response(
     *          response="404",
     *      ),
     *      @OA\Response(
     *          response="409",
     *      )
     * )
     * @Route("/formations/{id}", name="delete_formation", methods={"DELETE"})
     * @param EntityManagerInterface $entityManager
     * @param FormationRepository $formationRepository
     * @param Formation|null $formation
     * @return JsonResponse
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function DeleteFormationById(EntityManagerInterface $entityManager, FormationRepository $formationRepository, Formation $formation = null): JsonResponse
    {
        if ($formation == null)
            return new JsonResponse("La formation n'existe pas !", Response::HTTP_NOT_FOUND);

        $respo = $formation->getResponsable();

        $respo->removeFormation($formation);
        $entityManager->remove($formation);
        $formations = $respo->getFormations();

        if (sizeof($formations) == 0) {
            $personne = $respo->getPersonne();
            $personne->removeRole("ROLE_RESPO");
            $entityManager->remove($respo);
        } else {
            $entityManager->persist($respo);
        }

        $entityManager->flush();

        return new JsonResponse("Formation supprimée !", Response::HTTP_ACCEPTED);


        /*$repoResponse = $formationRepository->DeleteFormationById($entityManager,$formationRepository, $idFormation);

        switch ($repoResponse["status"]){
            case 200:
                return new JsonResponse("Ok",Response::HTTP_ACCEPTED);
                break;
            case 404:
                return new JsonResponse($repoResponse["error"],Response::HTTP_NOT_FOUND);
                break;
            case 409:
                return new JsonResponse($repoResponse["error"],Response::HTTP_CONFLICT);
                break;
            default:
                return new JsonResponse(Response::HTTP_NOT_FOUND);
        }*/
    }


    /**
     * @OA\Get(
     *      tags={"Formations"},
     *      path="/formations",
     *      @OA\Response(
     *          response="200",
     *          description ="get all formations",
     *          @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Formation"))
     *      )
     * )
     * @Route("/formations", name="formation_list", methods={"GET"})
     * @param FormationRepository $formationRepository
     * @param ResponsableRepository $responsableRepository
     * @return Response
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     * @Security("is_granted('ROLE_RESPO') or is_granted('ROLE_ADMIN')")
     */
    public function GetAllFormation(FormationRepository $formationRepository, ResponsableRepository $responsableRepository): Response
    {
        $user = $this->getUser();
        $username = null;
        if ($user != null) {
            $roles = $user->getRoles();
            $username = $user->getUsername();
        }

        if (in_array("ROLE_ADMIN", $roles)) {
            $formations = $formationRepository->findAll();

            $json = GenericSerializer::serializeJson($formations, ['groups' => 'get_formation']);

            return new JsonResponse($json, Response::HTTP_OK);
        } else if (in_array("ROLE_RESPO", $roles)) {
            $responsableConnected = null;
            if (!empty($username))
                $responsableConnected = $responsableRepository->findOneByUsername($username);

            $formations = $formationRepository->findAll();
            $formationsOfRespo = [];
            foreach ($formations as $formation) {
                if ($formation->getResponsable() == $responsableConnected) {
                    array_push($formationsOfRespo, $formation);
                }
            }
            $json = GenericSerializer::serializeJson($formationsOfRespo, ['groups' => 'get_formation']);

            return new JsonResponse($json, Response::HTTP_OK);
        } else {
            return new JsonResponse("Problème de rôle", Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *      tags={"Formations"},
     *      path="/formations",
     *      @OA\RequestBody(
     *          request="formation",
     *          @OA\JsonContent(
     *              @OA\Property(property="nom", ref="#/components/schemas/Formation/properties/nom"),
     *              @OA\Property(property="idPersonne", ref="#/components/schemas/Personne/properties/id"),
     *              @OA\Property(property="isAlternance", ref="#/components/schemas/Formation/properties/isAlternance")
     *          )
     *      ),
     *      @OA\Response(response="200", description="Formation ajoutée"),
     *      @OA\Response(response="400", description="Requête invalide"),
     *      @OA\Response(response="404", description="Erreur")
     * )
     * @Route("/formations", name="create_formation", methods={"POST"})
     * @param PersonneRepository $personneRepository
     * @param ResponsableRepository $responsableRepository
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param IntervenantRepository $intervenantRepository
     * @return JsonResponse
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function AddFormation(PersonneRepository $personneRepository, ResponsableRepository $responsableRepository, Request $request, EntityManagerInterface $entityManager, IntervenantRepository $intervenantRepository): Response
    {
        $data = json_decode($request->getContent(), true);
        $nameFormation = $data['nom'];
        $idPersonne = $data['idPersonne'];
        $isAlternance = $data['isAlternance'];

        $personne = $personneRepository->find($idPersonne);
        if ($personne == null) {
            return new JsonResponse("Personne avec l'ID '$idPersonne' inexistante !", Response::HTTP_NOT_FOUND);
        }

        if ($personne->hasRole("ROLE_ETUDIANT")) {
            return new JsonResponse("Un étudiant ne peux pas devenir responsable !", Response::HTTP_CONFLICT);
        }

        $intervenant = $intervenantRepository->findOneByUsername($personne->getUsername());
        if ($intervenant != null && $intervenant->getExterne()) {
            return new JsonResponse("Un intervenant [externe] ne peux pas devenir responsable !", Response::HTTP_CONFLICT);
        }

        //$personne->addRole("ROLE_RESPO");
        $respo = $responsableRepository->findOneByUsername($personne->getUsername());
        if ($respo == null) {
            $respo = new Responsable();
            $respo->setPersonne($personne);
        }

        $formation = new Formation();
        $formation->setNom($nameFormation);
        $formation->setIsAlternance($isAlternance);
        $formation->setResponsable($respo);

        $entityManager->persist($personne);
        $entityManager->persist($respo);
        $entityManager->persist($formation);

        $entityManager->flush();

        return new JsonResponse(GenericSerializer::serializeJson($formation, ["groups" => "get_formation"]), Response::HTTP_CREATED);

        /*if(empty($nameFormation) || empty($idPersonne))
        {

        }*/


        /*$repoResponse = $formationRepository->AjoutFormation($entityManager, $responsableRepository, $nameFormation, $idResponsable, $isAlternant);

        switch ($repoResponse["status"])
        {
            case 404:
                return new JsonResponse($repoResponse["error"],Response::HTTP_NOT_FOUND);
                break;
            case 400:
                return new JsonResponse($repoResponse["error"],Response::HTTP_BAD_REQUEST);
                break;
            case 200:
                $json = GenericSerializer::serializeJson($repoResponse["data"], ["groups" => "get_formation"]);
                return new JsonResponse($json,Response::HTTP_CREATED);
                break;
            default:
                return new JsonResponse($repoResponse["error"],Response::HTTP_NOT_FOUND);
        }*/
    }

    /**
     * @OA\Put(
     *      tags={"Formations"},
     *      path="/formations/{id}",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="id Formation",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          request="formation",
     *          @OA\JsonContent(
     *              @OA\Property(property="nom", ref="#/components/schemas/Formation/properties/nom"),
     *              @OA\Property(property="idPersonne", ref="#/components/schemas/Personne/properties/id"),
     *              @OA\Property(property="isAlternance", ref="#/components/schemas/Formation/properties/isAlternance")
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *      ),
     *     @OA\Response(
     *         response="404",
     *         description="La formation n'existe pas'",
     *     )
     * )
     * @Route("/formations/{id}", name="update_formation", methods={"PUT"})
     * @param PersonneRepository $personneRepository
     * @param EntityManagerInterface $entityManager
     * @param IntervenantRepository $intervenantRepository
     * @param Request $request
     * @param ResponsableRepository $responsableRepository
     * @param Formation|null $formation
     * @return JsonResponse
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function UpdateFormation(PersonneRepository $personneRepository, EntityManagerInterface $entityManager, IntervenantRepository $intervenantRepository, Request $request, ResponsableRepository $responsableRepository, Formation $formation = null): JsonResponse
    {
        if ($formation == null)
            return new JsonResponse("La formation n'existe pas !", Response::HTTP_NOT_FOUND);

        $respo_old = $formation->getResponsable();

        $data = json_decode($request->getContent(), true);
        $nameFormation = $data['nom'];
        $idPersonne = $data['idPersonne'];
        $isAlternance = $data['isAlternance'];

        $personne = $personneRepository->find($idPersonne);
        if ($personne == null) {
            return new JsonResponse("Personne avec l'ID '$idPersonne' inexistante !", Response::HTTP_NOT_FOUND);
        }

        if ($personne->hasRole("ROLE_ETUDIANT")) {
            return new JsonResponse("Un étudiant ne peux pas devenir responsable !", Response::HTTP_CONFLICT);
        }

        $intervenant = $intervenantRepository->findOneByUsername($personne->getUsername());
        if ($intervenant != null && $intervenant->getExterne()) {
            return new JsonResponse("Un intervenant [externe] ne peux pas devenir responsable !", Response::HTTP_CONFLICT);
        }

        $newRespo = $responsableRepository->findOneByUsername($personne->getUsername());
        if ($newRespo == null) {
            $newRespo = new Responsable();
            $newRespo->setPersonne($personne);
        }
        if ($respo_old->getId() != $newRespo->getId()) {
            $respo_old->removeFormation($formation);
            $formation->setResponsable($newRespo);
            $entityManager->persist($personne);
            $entityManager->persist($newRespo);
        }

        $formation->setIsAlternance($isAlternance);
        $formation->setNom($nameFormation);

        $entityManager->persist($formation);


        $formations = $respo_old->getFormations();

        if (sizeof($formations) == 0) {
            $personne = $respo_old->getPersonne();
            $personne->removeRole("ROLE_RESPO");
            $entityManager->remove($respo_old);
        } else {
            $entityManager->persist($respo_old);
        }

        $entityManager->flush();

        return new JsonResponse(GenericSerializer::serializeJson($formation, ["groups" => "get_formation"]), Response::HTTP_OK);

        /*$repoResponse = $formationRepository->UpdateFormation($formationRepository, $entityManager, $idFormation, $request, $responsableRepository);

        switch ($repoResponse["status"]) {
            case 201:
                $json = GenericSerializer::serializeJson($repoResponse["data"], ["groups" => "update_formation"]);
                return new JsonResponse($json,Response::HTTP_CREATED);
                break;
            case 404:
                return new JsonResponse($repoResponse["error"],Response::HTTP_NOT_FOUND);
                break;
            default:
                return new JsonResponse(Response::HTTP_NOT_FOUND);
        }*/
    }
}