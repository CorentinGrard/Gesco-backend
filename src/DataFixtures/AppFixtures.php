<?php

namespace App\DataFixtures;

use App\Entity\Matiere;
use App\Entity\Session;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use SessionType;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {

        $matiere = new Matiere();
        $matiere->setNom("TNI");
        $matiere->setCoefficient(2);
        $manager->persist($matiere);

        $matieres = [];

        for ($i = 0; $i < 10; $i++) {
            $matiere = new Matiere();
            $matiere->setNom(self::generateRandomString($i%5+5));
            $matiere->setCoefficient(2);
            array_push($matieres, $matiere);
            $manager->persist($matiere);
        }


        $bool = true;
        for ($i = 0; $i < 50; $i++) {
            $bool = !$bool;
            $session = new Session();
            $session->setMatiere($matieres[$i%10]);
            $session->setType(SessionType::values[$i%6+1]);
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
