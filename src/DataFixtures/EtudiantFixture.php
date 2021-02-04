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
        $personne->setAdresse("479 Avenue des euzières 34190 Brissac");
        //$personne->set Email("antonin.cabane@gmail.com");
        $personne->generateEmail(true);
        $personne->setNumeroTel("0750214383");


        $etudiant = new Etudiant();
        $etudiant->setPersonne($personne);
        $etudiant->setIsAlternant(true);

        $manager->persist($personne);
        $manager->persist($etudiant);
        $manager->flush();
    }
}
