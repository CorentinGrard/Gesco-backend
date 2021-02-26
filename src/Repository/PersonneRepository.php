<?php

namespace App\Repository;

use App\Entity\Personne;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use MongoDB\Driver\Exception\Exception;

/**
 * @method Personne|null find($id, $lockMode = null, $lockVersion = null)
 * @method Personne|null findOneBy(array $criteria, array $orderBy = null)
 * @method Personne[]    findAll()
 * @method Personne[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PersonneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Personne::class);
    }

    /**
     * @return Personne Returns an array of Personne objects
     */
    public function findOneByUsername($username)
    {
        try {
            return $this->createQueryBuilder('p')
                ->andWhere('p.email = :email')
                ->setParameter('email', $username)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            var_dump($e->getMessage());
        }
    }


    /*
    public function findOneBySomeField($value): ?Personne
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function getPersonnesEligiblentIntervenants(PersonneRepository $personneRepository, AssistantRepository $assistantRepository, ResponsableRepository $responsableRepository, IntervenantRepository $intervenantRepository)
    {
        $personnesEligibles = [];

        try {
            $assistants = $assistantRepository->findAll();
            foreach ($assistants as $assistant) {
                array_push($personnesEligibles,$assistant->getPersonne());
            }

            $responsables = $responsableRepository->findAll();
            foreach ($responsables as $responsable) {
                array_push($personnesEligibles,$responsable->getPersonne());
            }

            $intervenants = $intervenantRepository->findAll();
            foreach ($intervenants as $intervenant) {
                if (!$intervenant->getExterne()) {
                    array_push($personnesEligibles,$intervenant->getPersonne());
                }
            }
            return [
                "status" => 200,
                "data" => $personnesEligibles
            ];
        } catch(\Exception $e) {
            return [
                "status" => 500,
                "error" => $e
            ];
        }
    }
}
