<?php

namespace App\Repository;

use App\Entity\Formation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\MakerBundle\Str;

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

    public function AjoutFormation(EntityManager $entityManager, PersonneRepository $personneRepository, String $nomFormation, String $idResponsable, bool $isAlternant)
    {
        if (empty($nomFormation) || is_null($idResponsable) || empty($isAlternant)) {
            return[
                "status" => 400,
                "error"  => "Veuillez renseigner tous les champs"
            ];
        }

        if($this->CheckFormationExist($nomFormation)){
            return[
                "status" => 404,
                "error"  => "La formation existe déjà"
            ];
        }

        // TODO : ajouter une vérification du rôle du responsable ( id personne = un responsable )
        $responsable = $personneRepository->find($idResponsable);

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
    public function CheckFormationExist($nomFormation) : bool
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

    // /**
    //  * @return Formation[] Returns an array of Formation objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Formation
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
