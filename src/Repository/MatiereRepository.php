<?php

namespace App\Repository;

use App\Entity\Matiere;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Matiere|null find($id, $lockMode = null, $lockVersion = null)
 * @method Matiere|null findOneBy(array $criteria, array $orderBy = null)
 * @method Matiere[]    findAll()
 * @method Matiere[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MatiereRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Matiere::class);
    }

    public function deleteMatiereById(EntityManagerInterface $entityManager,MatiereRepository $matiereRepository,int $matiereId)
    {
        $currentMatiere = $matiereRepository->find($matiereId);

        if($currentMatiere == null){
            return[
                "status" => 404,
                "error"  => "La matière d'ID ".$matiereId." n'existe pas"
            ];
        }

        $entityManager->remove($currentMatiere);
        $entityManager->flush();

        return [
            "status" => 202,
            "error"  => null
        ];
    }

    public function getMatieresByPromotion(int $idPromotion)
    {


        $sql = "SELECT S.id as \"idSemestre\", S.nom as \"nomSemestre\", MO.id as \"idModule\", MO.nom as \"nomModule\", MA.id as \"idMatiere\", MA.nom as \"nomMatiere\"".
            " FROM Promotion P".
            " JOIN semestre S ON P.id=S.promotion_id".
            " JOIN module MO ON MO.semestre_id=S.id".
            " JOIN matiere MA ON MA.module_id=MO.id".
            " WHERE P.id = $idPromotion";

        $stmt = null;
        try {
            $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        } catch (Exception $e) {
            return null;
        }
        try {
            $stmt->execute([]);
        } catch (\Doctrine\DBAL\Driver\Exception $e) {
            return null;
        }

        $sqlResults = $stmt->fetchAll();

        $resultFormatted = [];
        $semestreAllreadyUsed = false;
        $moduleAllreadyUsed = false;

        foreach ($sqlResults as $result) {

            foreach ($resultFormatted as $elementsAdded) {
                if ($elementsAdded["idSemestre"] == $result["idSemestre"]) {
                    $semestreAllreadyUsed = true;
                }
            }

            if (!$semestreAllreadyUsed) {
                array_push($resultFormatted, [
                    "idSemestre" => $result["idSemestre"],
                    "nomSemestre" => $result["nomSemestre"],
                    "modules" => []
                ]);
            }

            $semestreKey = array_search($result["idSemestre"],array_column($resultFormatted,"idSemestre"));

            foreach ($resultFormatted[$semestreKey]["modules"] as $module) {
                if ($result["idModule"] == $module["idModule"]) {
                    $moduleAllreadyUsed = true;
                }
            }

            if(!$moduleAllreadyUsed) {

                array_push($resultFormatted[$semestreKey]["modules"], [
                    "idModule" => $result["idModule"],
                    "nomModule" => $result["nomModule"],
                    "matieres" => []
                ]);
            }

            $moduleKey = array_search($result["idModule"],array_column($resultFormatted[$semestreKey]["modules"],"idModule"));

            array_push($resultFormatted[$semestreKey]["modules"][$moduleKey]["matieres"],[
                "idMatiere" => $result["idMatiere"],
                "nomMatiere" => $result["nomMatiere"]
            ]);
        }
        return $resultFormatted;
    }


    // /**
    //  * @return Matiere[] Returns an array of Matiere objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Matiere
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
