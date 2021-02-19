<?php

namespace App\Repository;

use App\Entity\Promotion;
use App\Entity\Semestre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Semestre|null find($id, $lockMode = null, $lockVersion = null)
 * @method Semestre|null findOneBy(array $criteria, array $orderBy = null)
 * @method Semestre[]    findAll()
 * @method Semestre[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SemestreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Semestre::class);
    }

    // /**
    //  * @return Semestre[] Returns an array of Semestre objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Semestre
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function add(EntityManagerInterface $entityManager, Promotion $promotion, string $nom)
    {
        $semestre = new Semestre();
        $semestre->setNom($nom);
        $semestre->setPromotion($promotion);

        $entityManager->persist($semestre);
        $entityManager->flush();

        return [
            "status" => 201,
            "data" => $semestre,
            "error" => null
        ];
    }

    public function updateSemestre(EntityManagerInterface $entityManager, $nom, Semestre $semestre)
    {
        $semestre->setNom($nom);

        $entityManager->persist($semestre);
        $entityManager->flush();

        return [
            "status" => 201,
            "data" => $semestre,
            "error" => null
        ];
    }

    public function deleteSemestre(EntityManagerInterface $entityManager, Semestre $semestre): array
    {

        if (sizeof($semestre->getModules()) > 0) {
            return [
                "status" => 409,
                "data" => "Présence de module(s) dans le semestre, suppression impossible."
            ];
        }

        $entityManager->remove($semestre);
        $entityManager->flush();

        return [
            "status" => 202,
            "data" => "Semestre supprimé",
        ];
    }
}
