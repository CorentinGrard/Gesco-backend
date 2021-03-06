<?php

namespace App\Repository;

use App\Entity\Formation;
use App\Entity\Promotion;
use App\Entity\Responsable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method Promotion|null find($id, $lockMode = null, $lockVersion = null)
 * @method Promotion|null findOneBy(array $criteria, array $orderBy = null)
 * @method Promotion[]    findAll()
 * @method Promotion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PromotionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Promotion::class);
    }

    public function addEtudiantInPromotion(EntityManager $entityManager, EtudiantRepository $etudiantRepository,PromotionRepository $promotionRepository, int $idEtudiant, int $idPromotion) {

        $currentEtudiant = $etudiantRepository->find($idEtudiant);

        if($currentEtudiant == null) {
            return [
                "status" => 404,
                "error" => "L'étudiant d'ID ".$idEtudiant." n'existe pas"
            ];
        };

        if($currentEtudiant->getPromotion() == Null) {

            $currentPromotion = $promotionRepository->find($idPromotion);

            if($currentPromotion == null) {
                return [
                    "status" => 404,
                    "error" => "La promotion d'ID ".$idPromotion." n'existe pas"
                ];
            }

            $currentEtudiant->setPromotion($currentPromotion);

            $entityManager->persist($currentEtudiant);

            $entityManager->flush();
            return [
                "status"=>201,
                "error"=>null
            ];
        }
        else {
            return [
                "status"=>406,
                "error"=>"L'étudiant d'ID ".$idEtudiant." possède déjà une promotion"
            ];
        }
    }

    public function deleteEtudiantFromPromotion(EntityManagerInterface $entityManager,EtudiantRepository $etudiantRepository, int $idEtudiant)
    {
        $currentEtudiant = $etudiantRepository->find($idEtudiant);

        if($currentEtudiant == null) {
            return [
                "status" => 404,
                "error" => "L'étudiant d'ID ".$idEtudiant." n'existe pas"
            ];
        }

        if($currentEtudiant->getPromotion() == null) {
            return [
                "status" => 404,
                "error" => "L'étudiant d'ID ".$idEtudiant." ne possède pas de promotion"
            ];
        }
        else {
            $currentEtudiant->setPromotion(null);

            $entityManager->persist($currentEtudiant);

            $entityManager->flush();
            return [
                "status"=>202,
                "error"=>null
            ];

        }
    }

    public function updateEtudiantPromotion(EntityManagerInterface $entityManager,EtudiantRepository $etudiantRepository,PromotionRepository $promotionRepository,int $idEtudiant, int $newIdPromotion)
    {
        $currentEtudiant = $etudiantRepository->find($idEtudiant);

        if($currentEtudiant == null) {
            return [
                "status" => 404,
                "error" => "L'étudiant d'ID ".$idEtudiant." n'existe pas"
            ];
        }

        $currentPromotion = $promotionRepository->find($newIdPromotion);

        if($currentPromotion == null) {
            return [
                "status" => 404,
                "error" => "La promotion d'ID ".$newIdPromotion." n'existe pas"
            ];
        }

        $currentEtudiant->setPromotion($currentPromotion);

        $entityManager->persist($currentEtudiant);

        $entityManager->flush();

        return [
            "status"=>201,
            "error"=>null
        ];
    }

    public function getModulesByPromotion(Promotion $promotion, Responsable $responsableConnected)
    {
        $responsableCible = $promotion->getFormation()->getResponsable();

        if ($responsableCible === $responsableConnected) {
            if(!$promotion) {
                return [
                    "status"=>404,
                    "error"=>"La promotion n'existe pas"
                ];
            }

            return [
                "status"=>200,
                "data"=>$promotion
            ];
        }
        else {
            return [
                "status"=>403,
                "error"=>"Vous ne pouvez pas récupérer les modules des promotions visées car vous n'êtes pas responsable de cette promotion"
            ];
        }


    }

    private function CheckExistPromotionByName(string $namePromotion,Formation $formation) : bool
    {
        $listPromotion = $this->findAll();

        foreach ($listPromotion as $promotion){
            if($promotion->getNom() == $namePromotion && $formation->getNom() == $promotion->getFormation()->getNom()){
                return true;
            }
        }
        return false;
    }

    public function AjoutPromotion(EntityManagerInterface $entityManager, FormationRepository $formationRepository, PromotionRepository $promotionRepository, AssistantRepository $assistantRepository, Request $request, $responsableConnected)
    {
        $data = json_decode($request->getContent(), true);
        $namePromotion = $data['nom'];
        $idAssistant = $data['idAssistant'];
        $idFormation   = $data['idFormation'];

        $assistant = $assistantRepository->find($idAssistant);
        $formation = $formationRepository->find($idFormation);



        if(is_null($assistant)){
            return[
                "status" => 404,
                "error"  => "L'assistant d'ID ".$idAssistant." n'existe pas."
            ];
        }

        if(is_null($formation)){
            return[
                "status" => 404,
                "error"  => "La formation d'ID ".$idFormation." n'existe pas."
            ];
        }

        if($this->CheckExistPromotionByName($namePromotion, $formation)){
            return[
                "status" => 404,
                "error"  => "La promotion de nom  ".$namePromotion." existe déjà dans cette formation."
            ];
        }

        $responsableCible = $formation->getResponsable();

        if($responsableCible === $responsableConnected) {
            $promotion = new Promotion();
            $promotion->setAssistant($assistant);
            $promotion->setNom($namePromotion);
            $promotion->setFormation($formation);

            $entityManager->persist($promotion);
            $entityManager->flush();

            return [
                "status" => 201,
                "data" => $promotion,
                "error" => null
            ];
        }
        else {
            return [
                "status" => 403,
                "error" => "Vous ne pouvez pas creer une promotion dans la formation d'ID ".$idFormation." car vous n'etes pas responsable de cette formation"
            ];
        }

    }

    public function updatePromotion(EntityManagerInterface $entityManager, FormationRepository $formationRepository, AssistantRepository $assistantRepository, $nom, $formation_id, $assistant_id, Promotion $promotion, Responsable $responsableConnected)
    {
        if(!$promotion) {
            return [
                "status"=>409,
                "error"=>"La promotion n'existe pas"
            ];
        }

        $responsableCible = $promotion->getFormation()->getResponsable();

        $formation = $formationRepository->find($formation_id);

        if(!$formation) {
            return [
                "status"=>409,
                "error"=>"La formation d'ID ".$formation_id." n'existe pas"
            ];
        }

        $assistant = $assistantRepository->find($assistant_id);

        if(!$assistant) {
            return [
                "status"=>409,
                "error"=>"L'assistant(e) d'ID ".$assistant_id." n'existe pas"
            ];
        }

        if($responsableConnected === $responsableCible) {
            $promotion->setNom($nom);
            $promotion->setFormation($formation);
            $promotion->setAssistant($assistant);

            try {
                $entityManager->persist($promotion);
                $entityManager->flush();
                return [
                    "status"=>201,
                    "data"=>$promotion
                ];
            }
            catch (\Exception $e) {
                return [
                    "status"=>500,
                    "error"=>"Probleme lors de l'injection de la promotion en base de données"
                ];
            }
        }
        else {
            return [
                "status"=>403,
                "error"=>"Vous ne pouvez pas modifier de promotion dont vous n'êtes pas responsable"
            ];
        }


    }

    public function deletePromotion(EntityManagerInterface $entityManager, Promotion $promotion, Responsable $responsableConnected)
    {
        if(!$promotion) {
            return [
                "status"=>409,
                "error"=>"La promotion n'existe pas"
            ];
        }

        $responsableCible = $promotion->getFormation()->getResponsable();
        if ($responsableConnected === $responsableCible) {
            if (!(sizeof($promotion->getSemestres()) === 0)) {
                return [
                    "status"=>409,
                    "error"=>"La promotion ne peut pas être supprimée, elle comprend des semestres"
                ];
            }

            if (!(sizeof($promotion->getEtudiants()) === 0)) {
                return [
                    "status"=>409,
                    "error"=>"La promotion ne peut pas être supprimée, elle comprend des étudiants"
                ];
            }

            try {
                $entityManager->remove($promotion);
                $entityManager->flush();
                return [
                    "status"=>204,
                    "data"=>$promotion
                ];
            } catch (\Exception $e) {
                return [
                    "status"=>500,
                    "error"=>"La suppression de la promotion en base de données a échouée"
                ];
            }
        } else {
            return [
                "status"=>403,
                "error"=>"Vous ne pouvez pas supprimer une promotion dont voue n'êtes pas responsable"
            ];
        }

    }
}
