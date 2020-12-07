<?php

namespace App\Repository;

use App\Entity\Etudiant;
use App\Entity\Matiere;
use App\Entity\Note;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
