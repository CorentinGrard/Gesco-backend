<?php

namespace App\Repository;

use App\Entity\Intervenant;
use App\Entity\Matiere;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Matiere|null find($id, $lockMode = null, $lockVersion = null)
 * @method Matiere|null findOneBy(array $criteria, array $orderBy = null)
 * @method Matiere[]    findAll()
 * @method Matiere[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MatiereRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Matiere::class);
    }

    public function deleteMatiereById(EntityManagerInterface $entityManager, int $matiereId)
    {
        $currentMatiere = $this->find($matiereId);

        if($currentMatiere == null){
            return[
                "status" => 404,
                "error"  => "La matière d'ID ".$matiereId." n'existe pas"
            ];
        }

        $entityManager->remove($currentMatiere);
        $entityManager->flush();

        return [
            "status" => 202,
            "error"  => null
        ];
    }

    public function getMatieresByPromotion(int $idPromotion)
    {


        $sql = "SELECT S.id as \"idSemestre\", S.nom as \"nomSemestre\", MO.id as \"idModule\", MO.nom as \"nomModule\", MA.id as \"idMatiere\", MA.nom as \"nomMatiere\", MA.coefficient as \"coefficient\", MA.nombre_heures_aplacer as \"nombreHeuresAPlacer\"".
            " FROM Promotion P".
            " JOIN semestre S ON P.id=S.promotion_id".
            " JOIN module MO ON MO.semestre_id=S.id".
            " JOIN matiere MA ON MA.module_id=MO.id".
            " WHERE P.id = $idPromotion";

        $stmt = null;
        try {
            $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        } catch (Exception $e) {
            return null;
        }
        try {
            $stmt->execute([]);
        } catch (\Doctrine\DBAL\Driver\Exception $e) {
            return null;
        }

        $sqlResults = $stmt->fetchAll();

        $resultFormatted = [];
        $semestreAllreadyUsed = false;
        $moduleAllreadyUsed = false;

        foreach ($sqlResults as $result) {

            foreach ($resultFormatted as $elementsAdded) {
                if ($elementsAdded["idSemestre"] == $result["idSemestre"]) {
                    $semestreAllreadyUsed = true;
                }
            }

            if (!$semestreAllreadyUsed) {
                array_push($resultFormatted, [
                    "idSemestre" => $result["idSemestre"],
                    "nomSemestre" => $result["nomSemestre"],
                    "modules" => []
                ]);
            }

            $semestreKey = array_search($result["idSemestre"],array_column($resultFormatted,"idSemestre"));

            foreach ($resultFormatted[$semestreKey]["modules"] as $module) {
                if ($result["idModule"] == $module["idModule"]) {
                    $moduleAllreadyUsed = true;
                }
            }

            if(!$moduleAllreadyUsed) {

                array_push($resultFormatted[$semestreKey]["modules"], [
                    "idModule" => $result["idModule"],
                    "nomModule" => $result["nomModule"],
                    "matieres" => []
                ]);
            }

            $moduleKey = array_search($result["idModule"],array_column($resultFormatted[$semestreKey]["modules"],"idModule"));

            array_push($resultFormatted[$semestreKey]["modules"][$moduleKey]["matieres"],[
                "idMatiere" => $result["idMatiere"],
                "nomMatiere" => $result["nomMatiere"],
                "coefficient" => $result["coefficient"],
                "nombreHeuresAPlacer"=>$result["nombreHeuresAPlacer"]
            ]);
        }
        return $resultFormatted;
    }

    public function updateMatiere(EntityManagerInterface $entityManager, ModuleRepository $moduleRepository,Matiere $matiere, $nom, $coeff, $nbhp, $module_id,$responsableConnected)
    {
        if (!$matiere) {
            return [
                "status"=>409,
                "error"=>"La matiere à modifier n'existe pas"
            ];
        }

        $responsableCible = $matiere->getModule()->getSemestre()->getPromotion()->getFormation()->getResponsable();

        if ($responsableCible === $responsableConnected) {
            if ($coeff < 0) {
                return [
                    "status" => 409,
                    "error" => "Le coefficient ne peut pas être inférieur à zéro"
                ];
            }

            if ($nbhp < 0) {
                return [
                    "status" => 409,
                    "error" => "Le nombre d'heure à placer ne peut pas être inférieur à zéro"
                ];
            }

            $module = $moduleRepository->find($module_id);

            if (!$module) {
                return [
                    "status" => 409,
                    "error" => "Le module d'ID " . $module_id . " n'existe pas"
                ];
            }

            $matiere->setNom($nom);
            $matiere->setCoefficient($coeff);
            $matiere->setModule($module);
            $matiere->setNombreHeuresAPlacer($nbhp);

            try {
                $entityManager->persist($matiere);
                $entityManager->flush();
                return [
                    "status" => 201,
                    "data" => $matiere,
                    "error" => null
                ];
            } catch (\Exception $e) {
                return [
                    "status" => 500,
                    "error" => "Problème lors de l'injection de la matière en base de données"
                ];
            }
        }
        else {
            return [
                "status" => 403,
                "error" => "Vous ne pouvez pas modifier de matiere dont vous n'êtes pas responsable"
            ];
        }


    }

    public function addIntervenant(Matiere $matiere, Intervenant $intervenant)
    {
        $matiere->addIntervenant($intervenant);
        $this->_em->persist($matiere);
        $this->_em->persist($intervenant);
        $this->_em->flush();
    }

}
