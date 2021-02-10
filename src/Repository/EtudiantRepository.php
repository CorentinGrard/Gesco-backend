<?php

namespace App\Repository;

use App\Entity\Etudiant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\ResultSetMapping;
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
                ->innerJoin('e.Personne', 'p', 'WITH', 'p.id = e.Personne')
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
    {}
}
