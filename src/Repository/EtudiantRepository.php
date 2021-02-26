<?php

namespace App\Repository;

use App\Entity\Etudiant;
use App\Entity\Personne;
use App\Entity\Promotion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\ResultSetMapping;

use Doctrine\ORM\EntityManagerInterface;

use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Etudiant|null find($id, $lockMode = null, $lockVersion = null)
 * @method Etudiant|null findOneBy(array $criteria, array $orderBy = null)
 * @method Etudiant[]    findAll()
 * @method Etudiant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EtudiantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Etudiant::class);
    }

    /**
     * @return Etudiant Returns an array of Personne objects
     */
    public function findOneByUsername($username)
    {
/*
        $sql = "SELECT * FROM etudiant e " .
            "INNER JOIN personne p ON (p.id = e.personne_id) ".
            "WHERE p.email = :email";

        $rsm = new ResultSetMapping();

        $query = $this->_em->createNativeQuery($sql, $rsm);
        $query->setParameter('email', $username);

        return $query->getOneOrNullResult();
*/
        try {
            return $this->createQueryBuilder('e')
                ->join('e.Personne', 'p', 'WITH', 'p.id = e.Personne')
                ->where('p.email = :email')
                ->setParameter('email', $username)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            var_dump($e->getMessage());
        }
    }


    // /**
    //  * @return Etudiant[] Returns an array of Etudiant objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Etudiant
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function ajoutNoteEtudiant($idEtudiant,$idMatiere,$note): bool
    {

    }

    public function createEtudiantInPromotion(EntityManagerInterface $entityManager, Promotion $promotion, string $nom, string $prenom, string $adresse, string $numeroTel)
    {
        $personne = new Personne();
        $personne->setNom($nom);
        $personne->setPrenom($prenom);
        $personne->generateEmail(true);
        $personne->setAdresse($adresse);
        $personne->setNumeroTel($numeroTel);

        $entityManager->persist($personne);

        $etudiant = new Etudiant();
        $etudiant->setPersonne($personne);
        $etudiant->setPromotion($promotion);
        $entityManager->persist($etudiant);
        $entityManager->flush();

        return [
            "status" => 201,
            "data"=>$etudiant,
            "error" => "Etudiant correctement créé en base de données"
        ];
    }

    public function updateEtudiant(EntityManagerInterface $entityManager,PromotionRepository $promotionRepository, Etudiant $etudiant, $nom, $prenom, $adresse, $numeroTel, $promotion_id)
    {
        $promotion = $promotionRepository->find($promotion_id);
        if(!$promotion) {
            return [
                "status" => 409,
                "error" => "La promotion d'ID ".$promotion_id." n'existe pas"
            ];
        }

        $etudiant->setPromotion($promotion);

        $personne = $etudiant->getPersonne();
        $personne->setNom($nom);
        $personne->setPrenom($prenom);
        $personne->setAdresse($adresse);
        $personne->setNumeroTel($numeroTel);

        $entityManager->persist($personne);

        $etudiant->setPersonne($personne);
        $entityManager->persist($etudiant);
        $entityManager->flush();

        return [
            "status" => 201,
            "data" => $etudiant,
            "error" => "Etudiant correctement modifié en base de données"
        ];
    }
}
