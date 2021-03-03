<?php

namespace App\DataFixtures;

use App\Entity\Assistant;
use App\Entity\Etudiant;
use App\Entity\Formation;
use App\Entity\Intervenant;
use App\Entity\Note;
use App\Entity\Responsable;
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
use petstore\Pet;
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

        $responsables = [];

        $faker = Factory::create('fr_FR');

        for($i = 0; $i < 2; $i++){
            $personne = new Personne();

            $personne->setNom($faker->lastName);
            $personne->setPrenom($faker->firstName);
            $personne->setAdresse($faker->address);
            $personne->generateEmail(false);
            $personne->setNumeroTel($faker->phoneNumber);
//            $personne->setRoles(["ROLE_RESPO","ROLE_USER"]);

            $responsable = new Responsable();
            $responsable->setPersonne($personne);

            $manager->persist($personne);
            $manager->persist($responsable);

            array_push($responsables, $responsable);
        }

        //$faker = Factory::create();
        $yan = new Personne();

        $yan->setNom("Moret");
        $yan->setPrenom("Yan");
        $yan->setAdresse($faker->address);
        $yan->generateEmail(false);
        $yan->setNumeroTel($faker->phoneNumber);
        //$personne->setRoles(["ROLE_RESPO","ROLE_USER"]);

        $responsable = new Responsable();
        $responsable->setPersonne($yan);

        $manager->persist($yan);
        $manager->persist($responsable);
        array_push($responsables, $responsable);


        // AJOUT ADMIN //
        $personne = new Personne();
        $personne->setPrenom("Corentin");
        $personne->setNom("Grard");
        $personne->generateEmail(true);
        $personne->setNumeroTel("07XXXXXXXX");
        $personne->addRole("ROLE_ADMIN");
        $manager->persist($personne);


        //Ajout formations
        $formations = [];

        $formation = new Formation();
        $formation->setNom("CMC");
        $formation->setResponsable($responsables[0]);
        $formation->setIsAlternance(true);
        $manager->persist($formation);
        array_push($formations, $formation);

        $formation = new Formation();
        $formation->setNom("MKX");
        $formation->setResponsable($responsables[1]);
        $formation->setIsAlternance(true);
        $manager->persist($formation);
        array_push($formations, $formation);

        $formation = new Formation();
        $formation->setNom("INFRES");
        $formation->setResponsable($responsables[2]);
        $formation->setIsAlternance(true);
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

        $batimentValue = 'ABCDEFM';
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
        for($i = 0; $i < 2; $i++){
            //$faker = Factory::create();
            $personne = new Personne();

            $personne->setNom($faker->lastName);
            $personne->setPrenom($faker->firstName);
            $personne->setAdresse($faker->address);
            $personne->generateEmail(false);
            $personne->setNumeroTel($faker->phoneNumber);
//            $personne->setRoles(["ROLE_ASSISTANT","ROLE_USER"]);

            $assistant = new Assistant();
            $assistant->setPersonne($personne);
            $manager->persist($assistant);
            array_push($assistants, $assistant);
        }

        $personne = new Personne();

        $personne->setNom("Lecompère");
        $personne->setPrenom("Catherine");
        $personne->setAdresse("Quelque part");
        $personne->generateEmail(false);
        $personne->setNumeroTel("04XXXXXXXX");
//        $personne->setRoles(["ROLE_ASSISTANT","ROLE_USER"]);

        $assistant = new Assistant();
        $assistant->setPersonne($personne);
        $manager->persist($assistant);
        array_push($assistants, $assistant);


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

            $module = new Module();
            $module->setNom(self::generateRandomString(($k*$k)%5+10));
            try {
                $module->setEcts(random_int(0, 10));
            } catch (\Exception $e) {
            }
            $module->setSemestre($semestre);
            $manager->persist($module);

            array_push($modules, $module);
            $k++;
        }

        $intervenant = new Intervenant();
        $personne = new Personne();
        $personne->setPrenom($faker->firstName);
        $personne->setNom($faker->lastName);
        $personne->setEmail("intervenant.test@gmail.com");
        $intervenant->setPersonne($personne);
        $intervenant->setExterne(true);

        $manager->persist($personne);
        $manager->persist($intervenant);

        $intervenantYan = new Intervenant();
        $intervenantYan->setPersonne($yan);
        $intervenantYan->setExterne(false);

        $manager->persist($yan);
        $manager->persist($intervenantYan);


        $k = 0;
        $matieres = [];
        foreach ($modules as $module) {
            //$faker = Factory::create();

            $matiere = new Matiere();
            $matiere->setNom($faker->firstName);
            $matiere->setCoefficient($k % 4 + 1);
            $matiere->setModule($module);
            $matiere->setNombreHeuresAPlacer(($k % 5) * 1);
            $matiere->addIntervenant($intervenant);
            $manager->persist($matiere);
            array_push($matieres, $matiere);

            $matiere = new Matiere();
            $matiere->setNom($faker->firstName);
            $matiere->setCoefficient(($k*$k) % 4 + 1);
            $matiere->setModule($module);
            $matiere->setNombreHeuresAPlacer((($k*$k) % 5) * 1);
            $manager->persist($matiere);
            array_push($matieres, $matiere);

            $k++;
        }

        $mat = sizeof($matieres);
        $bool = true;
        for ($i = 0; $i < 20; $i++) {
            //$faker = Factory::create();

            $bool = !$bool;
            $session = new Session();
            $session->setMatiere($matieres[$i % $mat]);
            $session->setType(SessionType::values[$i % 6 + 1]);
            $session->setObligatoire($bool);
            $dateDebut = $faker->dateTimeBetween($startDate = '-5 days', $endDate = '+5 days');
            if(date('H', $dateDebut->getTimestamp()) < 8) {
                $dateDebut->setTime(8,0);
            }
            else if(date('H', $dateDebut->getTimestamp()) > 18) {
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
        $personne->setNom("Cabane");
        $personne->setAdresse("479 Avenue des euzières\n34190 Brissac");
        //$personne->set Email("antonin.cabane@gmail.com");
        $personne->generateEmail(true);
        $personne->setNumeroTel("0750214383");
//        $personne->setRoles(["ROLE_ETUDIANT","ROLE_USER"]);


        $etudiant1 = new Etudiant();
        $etudiant1->setPersonne($personne);
        $etudiant1->setPromotion($promotions[0]);

        $manager->persist($personne);
        $manager->persist($etudiant1);

        ///////

        // Création des étudiants

        $personne = new Personne();
        $personne->setPrenom("Adrien");
        $personne->setNom("Deconinck");
        $personne->setAdresse("30 allée des Nouilles\n30100 Alès");
        $personne->generateEmail(true);
        $personne->setNumeroTel("06XXXXXXXX");
//        $personne->setRoles(["ROLE_ETUDIANT","ROLE_USER"]);

        $etudiant2 = new Etudiant();
        $etudiant2->setPersonne($personne);
        $etudiant2->setPromotion($promotions[1]);

        $manager->persist($personne);

        foreach($matieres as $mat){
            $note = new Note();
            $note->setMatiere($mat);
            $note->setNote($faker->randomElement([10,11,12,13.5,15,16.7,17.1]));
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
//        $personne->setRoles(["ROLE_ETUDIANT","ROLE_USER"]);

        $etudiant3 = new Etudiant();
        $etudiant3->setPersonne($personne);
        $etudiant3->setPromotion($promotions[2]);


        $manager->persist($personne);

        foreach($matieres as $mat){
            $note = new Note();
            $note->setMatiere($mat);
            $note->setNote($faker->randomElement([10,11,12,13.5,15,16.7,17.1]));
            $etudiant3->addNote($note);
            $manager->persist($note);
        }

        $manager->persist($etudiant3);

        foreach($matieres as $mat){
            $note = new Note();
            $note->setEtudiant($etudiant3);
            $note->setMatiere($mat);
            $note->setNote($faker->randomElement([10,11,12,13.5,15,16.7,17.1]));
            $manager->persist($note);
        }

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
