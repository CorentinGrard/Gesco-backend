<?php

namespace App\Repository;

use App\Entity\Batiment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method Batiment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Batiment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Batiment[]    findAll()
 * @method Batiment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BatimentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Batiment::class);
    }


    public function AddBatiment(EntityManagerInterface $entityManager, Request $request,SiteRepository $siteRepository)
    {

        $data = json_decode($request->getContent(), true);

        $idSite = $data['idSite'];
        $nomBatiment = $data['nom'];

        if(empty($nomBatiment)){
            return[
                "status" => 400,
                "error"  => "Nom non renseigné"
            ];
        }

        if($this->CheckExistBatimentByName($nomBatiment)){
            return[
                "status" => 404,
                "error"  => "Le batiment de nom  ".$nomBatiment." existe déjà."
            ];
        }

        $currentSite = $siteRepository->find($idSite);

        if($currentSite == null){
            return[
                "status" => 400,
                "error"  => "L'ID site ".$idSite." n'existe pas."
            ];
        }

        $batiment = new Batiment();
        $batiment->setNomBatiment($nomBatiment);
        $batiment->setBatimentSite($currentSite);

        $entityManager->persist($batiment);
        $entityManager->flush();

        return [
            "status" => 201,
            "data"   =>$batiment,
            "error"  => null
        ];
    }

    private function CheckExistBatimentByName($nomBatiment)
    {
        $listBatiment = $this->findAll();

        foreach ($listBatiment as $batiment){
            if($nomBatiment == $batiment->getNomBatiment()){
                return true;
            }
        }
        return false;
    }
}
