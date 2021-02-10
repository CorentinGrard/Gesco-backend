<?php

namespace App\Repository;

use App\Entity\Promotion;
use App\Entity\Session;
use App\Serializers\SessionSerializer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
    public function allSessionsBetweenStartDateAndEndDateForPromotion(Promotion $promotion, string $startDateString, string $endDateString)
    {
        $sessions = $promotion->getSessions();

        $sessionArray = [];
        foreach($sessions as $session){
            if($session->getDateDebut() >= $dates["debut"] && $session->getDateFin() <= $dates["fin"]){
                array_push($sessionArray, $session);//->getArray());
            }
        }

        $json = SessionSerializer::serializeJson($sessionArray, ['groups'=>'session_get']);

        return new JsonResponse($json, Response::HTTP_OK);
    }


}
