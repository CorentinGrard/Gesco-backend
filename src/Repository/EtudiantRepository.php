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
use Psr\Log\LoggerInterface;

/**
 * @method Etudiant|null find($id, $lockMode = null, $lockVersion = null)
 * @method Etudiant|null findOneBy(array $criteria, array $orderBy = null)
 * @method Etudiant[]    findAll()
 * @method Etudiant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EtudiantRepository extends ServiceEntityRepository
{
    private $logger;

    public function __construct(ManagerRegistry $registry, LoggerInterface  $logger)
    {
        parent::__construct($registry, Etudiant::class);
        $this->logger = $logger;
    }

    /**
     * @return Etudiant Returns an array of Personne objects
     */
    public function findOneByUsername($username)
    {

        try {
            $sql = "SELECT e.id FROM etudiant e ".
                    "JOIN personne p ON e.personne_id = p.id AND p.id = e.personne_id WHERE p.email = :email";

            $conn = $this->getEntityManager()->getConnection();
            $stmt = $conn->prepare($sql);
            $stmt->execute(array('email' => $username));
            $result = $stmt->fetchOne();
            $etudiant = $this->find($result);
            return $etudiant;
        } catch (NonUniqueResultException $e) {
            var_dump($e->getMessage());
        }

        /*try {
            $query = $this->createQueryBuilder('e')
                ->leftJoin('e.Personne', 'p', 'WITH', 'p.id = e.Personne')
                ->where('p.email = :email')
                ->setParameter('email', $username)
                ->getQuery();
                $this->logger->warning("QUERY : " . $query->getSQL()
            );
            return $query->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            var_dump($e->getMessage());
        }*/
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
