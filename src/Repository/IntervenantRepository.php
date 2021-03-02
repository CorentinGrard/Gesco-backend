<?php

namespace App\Repository;

use App\Entity\Intervenant;
use App\Entity\Personne;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Intervenant|null find($id, $lockMode = null, $lockVersion = null)
 * @method Intervenant|null findOneBy(array $criteria, array $orderBy = null)
 * @method Intervenant[]    findAll()
 * @method Intervenant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IntervenantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Intervenant::class);
    }

    public function createIntervenant($nom, $prenom, $email, $adresse = null, $tel = null):Intervenant
    {
        $personne = new Personne();
        $personne->setNom($nom);
        $personne->setPrenom($prenom);
        $personne->setEmail($email);

        $personne->setAdresse($adresse);
        $personne->setNumeroTel($tel);


        $intervenant = new Intervenant();
        $intervenant->setPersonne($personne);

        $this->_em->persist($personne);
        $this->_em->persist($intervenant);

        $this->_em->flush();


        return $intervenant;
    }

    public function updateIntervenant(Intervenant $intervenant, $nom, $prenom, $email, $adresse = null, $tel = null):Intervenant
    {
        $personne = $intervenant->getPersonne();
        $personne->setNom($nom);
        $personne->setPrenom($prenom);
        $personne->setEmail($email);
        if($adresse != null){
            $personne->setAdresse($adresse);
        }
        if($tel != null){
            $personne->setNumeroTel($tel);
        }


        //$intervenant = new Intervenant();
        $intervenant->setPersonne($personne);

        $this->_em->persist($personne);
        $this->_em->persist($intervenant);

        $this->_em->flush();

        return $intervenant;
    }

    /**
     * @param $username
     * @return Intervenant|null
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function findOneByUsername($username)
    {
        try {
            $sql = "SELECT r.id FROM responsable r ".
                "JOIN personne p ON r.personne_id = p.id WHERE p.email = :email";

            $conn = $this->getEntityManager()->getConnection();
            $stmt = $conn->prepare($sql);
            $stmt->execute(array('email' => $username));
            $result = $stmt->fetchOne();
            $intervenant = $this->find($result);
            return $intervenant;
        } catch (NonUniqueResultException $e) {
            var_dump($e->getMessage());
        }
    }

    // /**
    //  * @return Intervenant[] Returns an array of Intervenant objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Intervenant
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
