<?php

namespace App\Repository;

use App\Entity\Module;
use App\Entity\Responsable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Module|null find($id, $lockMode = null, $lockVersion = null)
 * @method Module|null findOneBy(array $criteria, array $orderBy = null)
 * @method Module[]    findAll()
 * @method Module[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ModuleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Module::class);
    }

    // /**
    //  * @return Module[] Returns an array of Module objects
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
    public function findOneBySomeField($value): ?Module
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function deleteModule(EntityManagerInterface $entityManager, Module $module, Responsable $responsableConnected)
    {
        $responsableCible = $module->getSemestre()->getPromotion()->getFormation()->getResponsable();

        if($responsableCible === $responsableConnected) {
            if(sizeof($module->getMatieres()) === 0) {

                $entityManager->remove($module);
                $entityManager->flush();

                return [
                    "status" => 204,
                    "error" => "Etudiant correctement supprimé"
                ];
            }
            else {
                return [
                    "status" => 409,
                    "error" => "La module ne peut pas être supprimé car des matières le compose"
                ];
            }
        }
        else {
            return [
                "status" => 403,
                "error" => "Vous n'êtes pas responsable du modèle que vous voulez supprimer"
            ];
        }


    }

    public function updateModule(EntityManagerInterface $entityManager, SemestreRepository $semestreRepository, $nom, $ects, $semestre_id, Module $module, Responsable $responsableConnected): array
    {
        $responsableCible = $module->getSemestre()->getPromotion()->getFormation()->getResponsable();

        if ($responsableConnected === $responsableCible) {
            $semestre = $semestreRepository->find($semestre_id);

            if (!$semestre) {
                return [
                    "status" => 409,
                    "error" => "Le semestre d'ID " . $semestre_id . " n'existe pas"
                ];
            }

            if ($ects < 0) {
                return [
                    "status" => 409,
                    "error" => "L'ECTS doit ne peut pas être négatif"
                ];
            }

            $module->setNom($nom);
            $module->setEcts($ects);
            $module->setSemestre($semestre);

            try {
                $entityManager->persist($module);
                $entityManager->flush();
                return [
                    "status" => 201,
                    "data" => $module
                ];
            } catch (\Exception $e) {
                return [
                    "status" => 500,
                    "error" => "Probleme lors de l'injection du module dans la base de données"
                ];
            }
        }
        else {
            return [
                "status" => 403,
                "error" => "Vous n'êtes pas responsable du modèle que vous voulez modifier"
            ];
        }


    }


}
