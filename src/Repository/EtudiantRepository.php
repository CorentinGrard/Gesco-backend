<?php

namespace App\Repository;

use App\Entity\Etudiant;
use App\Entity\Personne;
use App\Entity\Promotion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
    {}

    public function createEtudiantInPromotion(EntityManagerInterface $entityManager, Promotion $promotion, string $nom, string $prenom, string $adresse, string $numeroTel, bool $isAlternant)
    {
        $personne = new Personne();
        $personne->setNom($nom);
        $personne->setPrenom($prenom);
        $personne->generateEmail(true);
        $personne->setAdresse($adresse);
        $personne->setNumeroTel($numeroTel);

        $entityManager->persist($personne);

        $etudiant = new Etudiant();
        $etudiant->setIsAlternant($isAlternant);
        $etudiant->setPersonne($personne);
        $etudiant->setPromotion($promotion);

        $entityManager->persist($etudiant);
        $entityManager->flush();



        return [
            "status" => 201,
            "error" => "Etudiant correctement créé en base de données"
        ];

    }

    public function updateEtudiant(EntityManagerInterface $entityManager, Etudiant $etudiant, $nom, $prenom, $adresse, $numeroTel, $isAlternant)
    {
        $personne = $etudiant->getPersonne();
        $personne->setNom($nom);
        $personne->setPrenom($prenom);
        $personne->generateEmail(true);
        $personne->setAdresse($adresse);
        $personne->setNumeroTel($numeroTel);

        $entityManager->persist($personne);

        $etudiant->setIsAlternant($isAlternant);
        $etudiant->setPersonne($personne);
        $entityManager->persist($etudiant);
        $entityManager->flush();

        return [
            "status" => 201,
            "error" => "Etudiant correctement modifié en base de données"
        ];
    }
}
