<?php

namespace App\DataFixtures;

use App\Entity\Assistant;
use App\Entity\Etudiant;
use App\Entity\Formation;
use App\Entity\Note;
use App\Entity\Site;
use App\Entity\Batiment;
use App\Entity\Salle;
use App\Entity\Matiere;
use App\Entity\Module;
use App\Entity\Personne;
use App\Entity\Promotion;
use App\Entity\Semestre;
use App\Entity\Session;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use SessionType;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        /* Commandes à effectuer EN DEV !!!
         * php bin/console doctrine:database:drop --force
         * php bin/console doctrine:database:create
         * php bin/console doctrine:schema:update --force
         * php bin/console doctrine:fixtures:load
         */

        //Ajout Responsable TODO : prévoir changement en fonction du changement des rôles

        $responsable = [];

        for($i = 0; $i < 3; $i++){
            $faker = Factory::create();
            $personne = new Personne();

            $personne->setNom($faker->lastName);
            $personne->setPrenom($faker->firstName);
            $personne->setAdresse($faker->address);
            $personne->generateEmail(false);
            $personne->setNumeroTel($faker->phoneNumber);

            $manager->persist($personne);
            array_push($responsable, $personne);
        }


        //Ajout formations
        $formations = [];

        $formation = new Formation();
        $formation->setNom("INFRES");
        $formation->setResponsable($responsable[0]);
        $manager->persist($formation);
        array_push($formations, $formation);

        $formation = new Formation();
        $formation->setNom("MKX");
        $formation->setResponsable($responsable[1]);
        $manager->persist($formation);
        array_push($formations, $formation);

        $formation = new Formation();
        $formation->setNom("CMC");
        $formation->setResponsable($responsable[2]);
        $manager->persist($formation);
        array_push($formations, $formation);


        //Ajout des sites
        $sites = [];

        $site = new Site();

        $site   ->setNomSite("Croupillac");
        $site   ->setAdress("7 rue Jules Renard, 30100 Alès");
        $manager->persist($site);
        array_push($sites,$site);

        $site = new Site();

        $site   ->setNomSite("Clavière");
        $site   ->setAdress("6 Avenue de Clavières, 30100 Alès");
        $manager->persist($site);
        array_push($sites,$site);

        //Ajout batiments

        $batimentValue = 'ABCDEF';
        $batiments = [];

        for ($i = 0; $i < strlen($batimentValue); $i++)
        {
            $batiment = new Batiment();

            $batiment->setNomBatiment($batimentValue[$i]);
            $batiment->setBatimentSite($sites[rand(0,count($sites) - 1 )]);
            $manager ->persist($batiment);
            array_push($batiments, $batiment);
        }

        //Ajout des salles

        $nbSalleParBatiment = 10;
        $salles = [];

        foreach ($batiments as $value)
        {
            for ($j = 0; $j < $nbSalleParBatiment; $j++)
            {
                $salle = new Salle();
                $salle  ->setBatiment($value);
                $salle  ->setNomSalle($value->getNomBatiment().'.'.$j);
                $manager->persist($salle);
                array_push($salles, $salle);
            }
        }

        $assistants = [];
        for($i = 0; $i < 3; $i++){
            $faker = Factory::create();
            $personne = new Personne();

            $personne->setNom($faker->lastName);
            $personne->setPrenom($faker->firstName);
            $personne->setAdresse($faker->address);
            $personne->generateEmail(false);
            $personne->setNumeroTel($faker->phoneNumber);

            $assistant = new Assistant();
            $assistant->setPersonne($personne);
            $manager->persist($assistant);
            array_push($assistants, $assistant);
        }

        $promotions = [];
        for ($i = 0; $i < 5; $i++) {
            for ($j = 0; $j < 3; $j++) {
                $promotion = new Promotion();;
                $promotion->setNom($i + 11);
                $promotion->setFormation($formations[$j]);
                $promotion->setAssistant($assistants[$j]);
                $manager->persist($promotion);
                array_push($promotions, $promotion);
            }
        }

        $k = 0;
        $semestres = [];
        foreach ($promotions as $promotion) {


            $semestre = new Semestre();
            $semestre->setNom("Semestre " . ($k + 1));
            $semestre->setPromotion($promotion);
            $manager->persist($semestre);

            array_push($semestres, $semestre);
            $k++;
        }

        $k = 0;
        $modules = [];
        foreach ($semestres as $semestre) {
            $module = new Module();
            $module->setNom(self::generateRandomString($k%5+10));
            $module->setEcts(3);
            $module->setSemestre($semestre);
            $manager->persist($module);

            array_push($modules, $module);
            $k++;
        }

        $k = 0;
        $matieres = [];
        foreach ($modules as $module) {
            $faker = Factory::create();

            $matiere = new Matiere();
            $matiere->setNom($faker->firstName);
            $matiere->setCoefficient($k % 4 + 1);
            $matiere->setModule($module);
            $matiere->setNombreHeuresAPlacer(($k % 5) * 1);
            $manager->persist($matiere);
            array_push($matieres, $matiere);

            $k++;
        }

        $mat = sizeof($matieres);
        $bool = true;
        for ($i = 0; $i < 20; $i++) {
            $faker = Factory::create();

            $bool = !$bool;
            $session = new Session();
            $session->setMatiere($matieres[$i % $mat]);
            $session->setType(SessionType::values[$i % 6 + 1]);
            $session->setObligatoire($bool);
            $dateDebut = $faker->dateTimeBetween($startDate = '-5 days', $endDate = '+5 days');
            if(date('H', $dateDebut->getTimestamp()) < 8) {
                $dateDebut->setTime(8,0);
            }elseif(date('H', $dateDebut->getTimestamp()) > 18) {
                $dateDebut->setTime(18,0);
            }
            $dateFin = clone $dateDebut;
            $heure = $faker->numberBetween(1,3);
            $min = $faker->randomElement([0,15,30,45]);
            $dateFin->add(new \DateInterval('PT'.$heure.'H'.$min.'M'));
            $session->setDateDebut($dateDebut);
            $session->setDateFin($dateFin);
            $session->addSessionSalle($salles[$i]);
            $session->setDetail($faker->randomAscii);

            $manager->persist($session);
        }


        // Ajout de personnes et d'étudiants

        $personne = new Personne();
        $personne->setPrenom("Antonin");
        $personne->setNom("CABANE");
        $personne->setAdresse("479 Avenue des euzières\n34190 Brissac");
        //$personne->set Email("antonin.cabane@gmail.com");
        $personne->generateEmail(true);
        $personne->setNumeroTel("0750214383");
        $personne->setRoles(["ROLE_ETUDIANT"]);


        $etudiant1 = new Etudiant();
        $etudiant1->setPersonne($personne);
        $etudiant1->setIsAlternant(true);
        $etudiant1->setPromotion($promotions[0]);

        $manager->persist($personne);
        $manager->persist($etudiant1);

        ///////

        // Création des étudiants

        $personne = new Personne();
        $personne->setPrenom("Guillaume");
        $personne->setNom("de Maleprade");
        $personne->setAdresse("40 Chemin des Pins\n30100 Alès");
        $personne->generateEmail(true);
        $personne->setNumeroTel("0668327467");
        $personne->setRoles(["ROLE_ETUDIANT"]);

        $etudiant2 = new Etudiant();
        $etudiant2->setPersonne($personne);
        $etudiant2->setIsAlternant(true);

        $manager->persist($personne);

        foreach($matieres as $mat){
            $note = new Note();
            $note->setMatiere($mat);
            $note->setNote($faker->randomElement([8,10,11,12,13.5,15,16.7,17.1]));
            $etudiant2->addNote($note);
            $manager->persist($note);
        }

        $manager->persist($etudiant2);

        //////

        $personne = new Personne();
        $personne->setPrenom("Matthieu");
        $personne->setNom("Tinnes");
        $personne->setAdresse("40 Chemin du Viget\n30100 Alès");
        $personne->generateEmail(true);
        $personne->setNumeroTel("07XXXXXXXX");
        $personne->setRoles(["ROLE_ETUDIANT"]);

        $etudiant3 = new Etudiant();
        $etudiant3->setPersonne($personne);
        $etudiant3->setIsAlternant(true);

        $manager->persist($personne);

        foreach($matieres as $mat){
            $note = new Note();
            $note->setMatiere($mat);
            $note->setNote($faker->randomElement([10,11,12,13.5,15,16.7,17.1]));
            $etudiant3->addNote($note);
            $manager->persist($note);
        }

        $manager->persist($etudiant3);


        $manager->flush();
    }

    public static function generateRandomString($size, $cstr = ''): string
    {
        $str_tmp = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $str = '';
        $maxI = rand($size > 5 ? $size - 5 : 0, $size + 5);
        for ($i = 0; $i < $maxI; $i++) {
            if (rand(0, 100) < 10) $str .= $cstr;
            $str .= $str_tmp[rand(0, strlen($str_tmp) - 1)];
        }
        return $str;
    }

}
