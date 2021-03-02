<?php

namespace App\Repository;

use App\Entity\Etudiant;
use App\Entity\Personne;
use App\Entity\Promotion;
use App\Entity\Responsable;
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
     * @param $username
     * @return Etudiant Returns an Etudiant object
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function findOneByUsername($username)
    {

        try {
            $sql = "SELECT e.id FROM etudiant e ".
                    "JOIN personne p ON e.personne_id = p.id WHERE p.email = :email";

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

    public function updateEtudiant(EntityManagerInterface $entityManager,PromotionRepository $promotionRepository, Etudiant $etudiant, $nom, $prenom, $adresse, $numeroTel, $promotion_id, Responsable $responsableConnected)
    {
        $promotion = $promotionRepository->find($promotion_id);
        if(!$promotion) {
            return [
                "status" => 409,
                "error" => "La promotion d'ID ".$promotion_id." n'existe pas"
            ];
        }

        $responsableCible = $etudiant->getPromotion()->getFormation()->getResponsable();

        if ($responsableCible === $responsableConnected) {

            $responsableCible = $promotion->getFormation()->getResponsable();
            if($responsableCible === $responsableConnected) {
                return [
                    "status" => 403,
                    "data" => null,
                    "error" => "Vous ne pouvez pas mettre un etudiant dans une promotion dont vous n'êtes pas responsable"
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
        else {
            return [
                "status" => 403,
                "data" => null,
                "error" => "Vous ne pouvez pas modifier un etudiant qui n'est pas dans une promotion dont vous êtes responsable"
            ];
        }
    }
}
