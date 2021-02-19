<?php

namespace App\Repository;

use App\Entity\Responsable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Responsable|null find($id, $lockMode = null, $lockVersion = null)
 * @method Responsable|null findOneBy(array $criteria, array $orderBy = null)
 * @method Responsable[]    findAll()
 * @method Responsable[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResponsableRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Responsable::class);
    }

    /**
     * @return Responsable Returns an array of Personne objects
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
            return $this->createQueryBuilder('r')
                ->innerJoin('r.Personne', 'p', 'WITH', 'p.id = r.Personne')
                ->where('p.email = :email')
                ->setParameter('email', $username)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            var_dump($e->getMessage());
        }
    }

    // /**
    //  * @return Responsable[] Returns an array of Responsable objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Responsable
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
