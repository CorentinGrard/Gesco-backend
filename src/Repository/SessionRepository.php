<?php

namespace App\Repository;

use App\Entity\Assistant;
use App\Entity\Etudiant;
use App\Entity\Matiere;
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
    public function allSessionsBetweenStartDateAndEndDateForPromotionAssistant(Promotion $promotion, $startDate, $endDate, Assistant $assistantConnected): array
    {

        $sessions = $promotion->getSessions();

        if ($promotion->getAssistant() === $assistantConnected) {
            $sessionArray = [];
            foreach($sessions as $session) {
                if($session->getDateDebut() >= $startDate && $session->getDateFin() <= $endDate && $session->getMatiere()->getModule()->getSemestre()->getPromotion()->getAssistant() === $assistantConnected){
                    array_push($sessionArray, $session);//->getArray());
                }
            }
            return [
                "status" => 200,
                "data" => $sessionArray
            ];
        }
        else {
            return [
                "status" => 403,
                "error" => "Vous ne pouvez pas voir les sessions des promotions dont vous n'??tes pas responsable"
            ];
        }


    }

    public function allSessionsBetweenStartDateAndEndDateForPromotionAdmin(Promotion $promotion, $startDate, $endDate): array
    {

        $sessions = $promotion->getSessions();

        $sessionArray = [];
        foreach($sessions as $session) {
            if($session->getDateDebut() >= $startDate && $session->getDateFin() <= $endDate){
                array_push($sessionArray, $session);//->getArray());
            }
        }

        return [
            "status" => 200,
            "data" => $sessionArray
        ];
    }

    public function allSessionsBetweenStartDateAndEndDateForPromotionEtudiant(Promotion $promotion, $startDate, $endDate): array
    {

        $sessions = $promotion->getSessions();

        $sessionArray = [];
        foreach($sessions as $session) {
            if($session->getDateDebut() >= $startDate && $session->getDateFin() <= $endDate){
                array_push($sessionArray, $session);
            }
        }

        return [
            "status" => 200,
            "data" => $sessionArray
        ];
    }

    public function updateSession(EntityManagerInterface $entityManager, Session $session, string $type, $dateDebut, $dateFin, $detail, Matiere $matiere, bool $obligatoire=false)
    {
        $session->setDetail($detail);
        $session->setType($type);
        $session->setObligatoire($obligatoire);
        try {
            $session->setDateDebut(new \DateTime($dateDebut));
        } catch (\Exception $e) {
        }
        $session->setDateFin(new \DateTime($dateFin));
        $session->setMatiere($matiere);

        $entityManager->persist($session);
        $entityManager->flush();

        return $session;
    }


    public function deleteSession(EntityManagerInterface $entityManager, Session $session)
    {
        $entityManager->remove($session);
        $entityManager->flush();

        return [
            "status" => 200,
            "data" => "Sessions correctement supprim??e"
        ];

    }


}
