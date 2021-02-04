<?php

namespace App\DataFixtures;

use App\Entity\Assistant;
use App\Entity\Formation;
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

        $formations = [];

        $formation = new Formation();
        $formation->setNom("INFRES");
        $manager->persist($formation);
        array_push($formations, $formation);

        $formation = new Formation();
        $formation->setNom("MKX");
        $manager->persist($formation);
        array_push($formations, $formation);

        $formation = new Formation();
        $formation->setNom("CMC");
        $manager->persist($formation);
        array_push($formations, $formation);

        $assistants = [];
        for($i = 0; $i < 3;$i++){
            $faker = Factory::create();
            $personne = new Personne();

            $personne->setNom($faker->lastName);
            $personne->setPrenom($faker->firstName);
            $personne->setAdresse($faker->address);
            $personne->setEmail($faker->email);
            $personne->setNumeroTel($faker->phoneNumber);

            $assistant = new Assistant();
            $assistant->setPersonne($personne);
            $manager->persist($assistant);
            array_push($assistants, $assistant);
        }

        $promotions = [];
        for ($i = 0; $i < 5; $i++) {
            for ($j = 0; $j < 3; $j++) {
                $promotion = new Promotion();
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
            $matiere = new Matiere();
            $matiere->setNom(self::generateRandomString($k % 5 + 10));
            $matiere->setCoefficient($k % 4 + 1);
            $matiere->setModule($module);
            $matiere->setNombreHeuresAPlacer($k % 5 + 1);
            $manager->persist($matiere);
            array_push($matieres, $matiere);

            $k++;
        }

        $mat = sizeof($matieres);
        $bool = true;
        for ($i = 0; $i < 100; $i++) {
            $bool = !$bool;
            $session = new Session();
            $session->setMatiere($matieres[$i % $mat]);
            $session->setType(SessionType::values[$i % 6 + 1]);
            $session->setObligatoire($bool);
            $session->setDateDebut(new DateTime());
            $session->setDateFin(new DateTime());
            $manager->persist($session);
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
