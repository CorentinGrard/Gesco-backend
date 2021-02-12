<?php

namespace App\Repository;

use App\Entity\Promotion;
use App\Entity\Session;
use App\Serializers\SessionSerializer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @method Session|null find($id, $lockMode = null, $lockVersion = null)
 * @method Session|null findOneBy(array $criteria, array $orderBy = null)
 * @method Session[]    findAll()
 * @method Session[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Session::class);
    }

    /*
    public function findOneBySomeField($value): ?Session
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function allSessionsBetweenStartDateAndEndDateForPromotion(Promotion $promotion, $startDate, $endDate)
    {
        $sessions = $promotion->getSessions();

        $sessionArray = [];
        foreach($sessions as $session) {
            if($session->getDateDebut() >= $startDate && $session->getDateFin() <= $endDate){
                array_push($sessionArray, $session);//->getArray());
            }
        }


        return $sessionArray;
    }

    public function updateSession(EntityManagerInterface $entityManager, Session $session, string $type, bool $obligatoire, $dateDebut, $dateFin, $detail)
    {
        $session->setDetail($detail);
        $session->setType($type);
        $session->setObligatoire($obligatoire);
        try {
            $session->setDateDebut(new \DateTime($dateDebut));
        } catch (\Exception $e) {
        }
        $session->setDateFin(new \DateTime($dateFin));

        $entityManager->persist($session);
        $entityManager->flush();

        return $session;
    }


}
