<?php

namespace App\Repository;

use App\Entity\Formation;
use App\Entity\Personne;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method Formation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Formation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Formation[]    findAll()
 * @method Formation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FormationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Formation::class);
    }

    public function AjoutFormation(EntityManager $entityManager, ResponsableRepository $responsableRepository, String $nomFormation, String $idResponsable, bool $isAlternant)
    {
        if (empty($nomFormation) || is_null($idResponsable) || empty($isAlternant)) {
            return[
                "status" => 400,
                "error"  => "Veuillez renseigner tous les champs"
            ];
        }

        if($this->CheckFormationExistByName($nomFormation)){
            return[
                "status" => 404,
                "error"  => "La formation existe déjà"
            ];
        }

        // TODO : ajouter une vérification du rôle du responsable ( id personne = un responsable )
        $responsable = $responsableRepository->find($idResponsable);

        if($responsable == null){
            return[
                "status" => 404,
                "error"  => "Le responsable n'existe pas"
            ];
        }

        $formation = new Formation();
        $formation->setNom($nomFormation);
        $formation->setResponsable($responsable);
        $formation->setIsAlternance($isAlternant);

        $entityManager->persist($formation);
        $entityManager->flush();

        return[
            "status" => 200,
            "error"  => null
        ];
    }

    /**
     * @param string $nomFormation
     * @return bool
     */
    public function CheckFormationExistByName($nomFormation) : bool
    {
        $listFormation = $this->findAll();

        foreach ($listFormation as $formation){
            if($formation->getNom() == $nomFormation){
                return true;
            }
        }
        return false;
    }

    public function DeleteFormationById(EntityManagerInterface $entityManager, FormationRepository $formationRepository, int $formationId)
    {
        $currentFormation = $formationRepository->find($formationId);

        if($currentFormation == null){
            return[
                "status" => 404,
                "error"  => "La formation d'ID ".$formationId." n'existe pas"
            ];
        }

        if(count($currentFormation->getPromotions()) > 1){
            return[
                "status" => 409,
                "error"  => "Il existe des promotions dans cette formation."
            ];
        }

        $entityManager->remove($currentFormation);
        $entityManager->flush();

        return [
            "status" => 200,
            "error"  => null
        ];
    }

    public function UpdateFormation(FormationRepository $formationRepository, EntityManagerInterface $entityManager ,int $idFormation, Request $request, PersonneRepository $personneRepository){

        $currentFormation = $formationRepository->find($idFormation);

        if(is_null($currentFormation)){
            return[
                "status" => 404,
                "error"  => "La formation d'ID ".$idFormation." n'existe pas"
            ];
        }

        $data = json_decode($request->getContent(), true);

        $name = $data["nom"];
        $idResponsable = $data["idResponsable"];
        $isAlternance = $data["isAlternance"];

        $responsable = $personneRepository->find($idResponsable);

        if(is_null($responsable)){
            return[
                "status" => 404,
                "error"  => "Le responsable d'Id ".$idResponsable." n'existe pas."
            ];
        }

        $currentFormation->setIsAlternance($isAlternance);
        $currentFormation->setNom($name);
        $currentFormation->setResponsable($responsable);

        $entityManager->persist($currentFormation);
        $entityManager->flush();

        return [
            "status" => 201,
            "data" => $currentFormation,
            "error" => "Formation correctement modifiée."
        ];
    }
}
