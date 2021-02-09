<?php

namespace App\DataFixtures;

use App\Entity\Etudiant;
use App\Entity\Personne;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class EtudiantFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $personne = new Personne();
        $personne->setPrenom("Antonin");
        $personne->setNom("CABANE");
        $personne->setAdresse("479 Avenue des euzières\n34190 Brissac");
        //$personne->set Email("antonin.cabane@gmail.com");
        $personne->generateEmail(true);
        $personne->setNumeroTel("0750214383");


        $etudiant = new Etudiant();
        $etudiant->setPersonne($personne);
        $etudiant->setIsAlternant(true);

        $manager->persist($personne);
        $manager->persist($etudiant);

        ///////

        $personne = new Personne();
        $personne->setPrenom("Guillaume");
        $personne->setNom("de Maleprade");
        $personne->setAdresse("40 Chemin des Pins\n30100 Alès");
        $personne->generateEmail(true);
        $personne->setNumeroTel("0668327467");

        $etudiant = new Etudiant();
        $etudiant->setPersonne($personne);
        $etudiant->setIsAlternant(true);

        $manager->persist($personne);
        $manager->persist($etudiant);

        ///////

        $manager->flush();
    }
}
