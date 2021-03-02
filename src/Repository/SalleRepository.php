<?php

namespace App\Repository;

use App\Entity\Salle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method Salle|null find($id, $lockMode = null, $lockVersion = null)
 * @method Salle|null findOneBy(array $criteria, array $orderBy = null)
 * @method Salle[]    findAll()
 * @method Salle[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SalleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Salle::class);
    }

    public function findAllSalle()
    {
        $qb = $this->findAll();
        return $qb;
    }

    public function AddSalle(EntityManagerInterface $entityManager, Request $request, BatimentRepository $batimentRepository)
    {
        $data = json_decode($request->getContent(), true);

        $idBatiment = $data['idBatiment'];
        $nomSalle = $data['nom'];

        if(empty($nomSalle)){
            return[
                "status" => 400,
                "error"  => "Nom non renseigné"
            ];
        }

        if($this->CheckExistSalleByName($nomSalle)){
            return[
                "status" => 404,
                "error"  => "La salle de nom  ".$nomSalle." existe déjà."
            ];
        }

        $currentBatiment = $batimentRepository->find($idBatiment);

        if($currentBatiment == null){
            return[
                "status" => 400,
                "error"  => "L'ID batiment ".$idBatiment." n'existe pas."
            ];
        }

        $salle = new Salle();
        $salle->setBatiment($currentBatiment);
        $salle->setNomSalle($nomSalle);

        $entityManager->persist($salle);
        $entityManager->flush();

        return [
            "status" => 201,
            "data"   =>$salle,
            "error"  => null
        ];
    }

    private function CheckExistSalleByName($nomSalle)
    {
        $listSalle = $this->findAll();

        foreach ($listSalle as $salle){
            if($nomSalle == $salle->getNomSalle()){
                return true;
            }
        }
        return false;
    }
}
