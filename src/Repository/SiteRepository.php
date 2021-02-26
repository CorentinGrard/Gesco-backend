<?php

namespace App\Repository;

use App\Entity\Site;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method Site|null find($id, $lockMode = null, $lockVersion = null)
 * @method Site|null findOneBy(array $criteria, array $orderBy = null)
 * @method Site[]    findAll()
 * @method Site[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SiteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Site::class);
    }

    public function AddSite(EntityManagerInterface $entityManager, Request $request)
    {
        $data = json_decode($request->getContent(), true);

        $nameSite = $data['nom'];
        $adresseSite = $data['adresse'];

        if(empty($nameSite) || empty($adresseSite)){
            return[
                "status" => 400,
                "error"  => "Adresse ou nom non renseignés"
            ];
        }

        if($this->CheckExistSiteByName($nameSite)){
            return[
                "status" => 404,
                "error"  => "Le site de nom  ".$nameSite." existe déjà."
            ];
        }

        $site = new Site();
        $site->setAdress($adresseSite);
        $site->setNomSite($nameSite);

        $entityManager->persist($site);
        $entityManager->flush();

        return [
            "status" => 201,
            "data"   =>$site,
            "error"  => null
        ];
    }

    private function CheckExistSiteByName($nameSite)
    {
        $listSite = $this->findAll();

        foreach ($listSite as $site){
            if($nameSite == $site->getNomSite()){
                return true;
            }
        }
        return false;
    }
}
