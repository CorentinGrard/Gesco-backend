<?php

namespace App\Repository;

use App\Entity\Assistant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Assistant|null find($id, $lockMode = null, $lockVersion = null)
 * @method Assistant|null findOneBy(array $criteria, array $orderBy = null)
 * @method Assistant[]    findAll()
 * @method Assistant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AssistantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Assistant::class);
    }

    /**
     * @param $username
     * @return Assistant Returns an array of Personne objects
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function findOneByUsername($username)
    {
        try {
            $sql = "SELECT a.id FROM assistant a ".
                "JOIN personne p ON a.personne_id = p.id WHERE p.email = :email";

            $conn = $this->getEntityManager()->getConnection();
            $stmt = $conn->prepare($sql);
            $stmt->execute(array('email' => $username));
            $result = $stmt->fetchOne();
            $etudiant = $this->find($result);
            return $etudiant;
        } catch (NonUniqueResultException $e) {
            var_dump($e->getMessage());
        }
    }

    // /**
    //  * @return Assistant[] Returns an array of Assistant objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Assistant
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
