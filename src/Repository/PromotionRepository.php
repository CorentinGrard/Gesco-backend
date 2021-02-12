<?php

namespace App\Repository;

use App\Entity\Promotion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Promotion|null find($id, $lockMode = null, $lockVersion = null)
 * @method Promotion|null findOneBy(array $criteria, array $orderBy = null)
 * @method Promotion[]    findAll()
 * @method Promotion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PromotionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Promotion::class);
    }

    // /**
    //  * @return Promotion[] Returns an array of Promotion objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Promotion
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */


    public function addEtudiantInPromotion(EntityManager $entityManager, EtudiantRepository $etudiantRepository,PromotionRepository $promotionRepository, int $idEtudiant, int $idPromotion) {

        $currentEtudiant = $etudiantRepository->find($idEtudiant);

        if($currentEtudiant == null) {
            return [
                "status" => 404,
                "error" => "L'étudiant d'ID ".$idEtudiant." n'existe pas"
            ];
        };

        if($currentEtudiant->getPromotion() == Null) {

            $currentPromotion = $promotionRepository->find($idPromotion);

            if($currentPromotion == null) {
                return [
                    "status" => 404,
                    "error" => "La promotion d'ID ".$idPromotion." n'existe pas"
                ];
            }

            $currentEtudiant->setPromotion($currentPromotion);

            $entityManager->persist($currentEtudiant);

            $entityManager->flush();
            return [
                "status"=>201,
                "error"=>null
            ];
        }
        else {
            return [
                "status"=>406,
                "error"=>"L'étudiant d'ID ".$idEtudiant." possède déjà une promotion"
            ];
        }
    }

    public function deleteEtudiantFromPromotion(EntityManagerInterface $entityManager,EtudiantRepository $etudiantRepository, int $idEtudiant)
    {
        $currentEtudiant = $etudiantRepository->find($idEtudiant);

        if($currentEtudiant == null) {
            return [
                "status" => 404,
                "error" => "L'étudiant d'ID ".$idEtudiant." n'existe pas"
            ];
        }

        if($currentEtudiant->getPromotion() == null) {
            return [
                "status" => 404,
                "error" => "L'étudiant d'ID ".$idEtudiant." ne possède pas de promotion"
            ];
        }
        else {
            $currentEtudiant->setPromotion(null);

            $entityManager->persist($currentEtudiant);

            $entityManager->flush();
            return [
                "status"=>202,
                "error"=>null
            ];

        }
    }

    public function updateEtudiantPromotion(EntityManagerInterface $entityManager,EtudiantRepository $etudiantRepository,PromotionRepository $promotionRepository,int $idEtudiant, int $newIdPromotion)
    {
        $currentEtudiant = $etudiantRepository->find($idEtudiant);

        if($currentEtudiant == null) {
            return [
                "status" => 404,
                "error" => "L'étudiant d'ID ".$idEtudiant." n'existe pas"
            ];
        }

        $currentPromotion = $promotionRepository->find($newIdPromotion);

        if($currentPromotion == null) {
            return [
                "status" => 404,
                "error" => "La promotion d'ID ".$newIdPromotion." n'existe pas"
            ];
        }

        $currentEtudiant->setPromotion($currentPromotion);

        $entityManager->persist($currentEtudiant);

        $entityManager->flush();

        return [
            "status"=>201,
            "error"=>null
        ];


    }
}
