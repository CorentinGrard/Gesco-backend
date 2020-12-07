<?php

namespace App\Repository;

use App\Entity\Etudiant;
use App\Entity\Matiere;
use App\Entity\Note;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Note|null find($id, $lockMode = null, $lockVersion = null)
 * @method Note|null findOneBy(array $criteria, array $orderBy = null)
 * @method Note[]    findAll()
 * @method Note[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Note::class);
    }

    /**
     * @param EntityManager $entityManager
     * @param EtudiantRepository $etudiantRepository
     * @param MatiereRepository $matiereRepository
     * @param int $idEtudiant
     * @param int $idMatiere
     * @param int $note
     * @return void Returns an array of Note objects
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */

    public function ajoutNoteEtudiant(EntityManager $entityManager, EtudiantRepository $etudiantRepository, MatiereRepository $matiereRepository, int $idEtudiant,int $idMatiere,int $valeur)
    {

        $note = new Note();
        $etudiant = $etudiantRepository->find($idEtudiant);
        $matiere = $matiereRepository->find($idMatiere);

        
        $note->setEtudiant($etudiant);
        $note->setMatiere($matiere);
        $note->setNote((float) $valeur);

        $this->checkNoteDejaRenseignee($note, $etudiant, $matiere, $valeur);

        $entityManager->persist($note);
        $entityManager->flush();
    }

    /**
     * @param Note $note
     * @param Etudiant $etudiant
     * @param Matiere $matiere
     * @param float $valeurNote
     * @return bool
     */
    public function checkNoteDejaRenseignee(Note $note, Etudiant $etudiant, Matiere $matiere, float $valeurNote): bool
    {

        $notesEtudiant = $etudiant->getNotes();

        foreach ($notesEtudiant as $noteCourante) {
            if ($noteCourante->getEtudiant() == $etudiant && $noteCourante->getMatiere() == $matiere && $noteCourante->getNote() == $valeurNote) {
                return true;
            }
        }

        return false;
    }

    public function getAllNotesBySemestre(int $idEtudiant, int $idSemestre)
    {

        $sql = "SELECT S.id as \"IDSEMESTRE\", S.nom as \"NOMSEMESTRE\", MO.id as \"IDMODULE\", MO.nom as \"NOMMODULE\", MA.id as \"IDMATIERE\", MA.nom as \"NOMMATIERE\", N.note, MA.coefficient".
                " FROM Semestre S".
                " JOIN module MO ON MO.semestre_id=S.id".
                " JOIN matiere MA ON MA.module_id=MO.id".
                " JOIN note N ON N.matiere_id=MA.id".
                " JOIN etudiant E ON N.etudiant_id=E.id".
                " WHERE E.id = $idEtudiant AND S.id = $idSemestre".
                " GROUP BY S.id";

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

        $result = $stmt->fetchAll();

        $resultFormatted = [];

       # "IDSEMESTRE": "1",
       # "NOMSEMESTRE": "Semestre 1",
       # "IDMODULE": "1",
       # "NOMMODULE": "8n4BfmIzFJ",
       # "IDMATIERE": "1",
       # "NOMMATIERE": "oqsQatqRL6VQ",
       # "note": "19",#"coefficient": "1"

        $modules = [];

        foreach ($result as $elem) {
            $idModule = $elem["IDMODULE"];
            $idMatiere = $elem["IDMATIERE"];
            $nomModule = $elem["NOMMODULE"];
            $nomMatiere = $elem["NOMMATIERE"];
            $note = $elem["NOTE"];

            foreach($modules as $module){
                if()
            }

        }



        return $result;
    }

    // /**
    //  * @return Note[] Returns an array of Note objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('n.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Note
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
