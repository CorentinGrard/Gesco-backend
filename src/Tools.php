<?php


namespace App;

class Tools
{
    static function getDatesMonToFri($dateString = '')
    {
        $date = new \DateTime();
        $dateDebut = new \DateTime();
        $dateFin = new \DateTime();
        if(!empty($dateString)){
            $year = substr($dateString, 0,4);
            $month = substr($dateString, 4,2);
            $day = substr($dateString, 6,2);
            $unixtimestamp = strtotime($year ."-". $month ."-". $day);
            $date->setTimestamp($unixtimestamp);
            #$dateDebut->setTimestamp($unixtimestamp);
            #$dateFin->setTimestamp($unixtimestamp);
        }
        if($date->format('D') == "Mon") {
            $dateDebut->setTime(0, 0);
        }else{
            $dateDebut->setTimestamp(strtotime("previous monday", $date->getTimestamp()));
        }
        $dateFin->setTimestamp(strtotime("+5 days", $dateDebut->getTimestamp()));

        return ["debut"=>$dateDebut,"fin"=>$dateFin];
    }

    static function getDateByStringDate($dateString = '')
    {
        $date = new \DateTime();
        if(!empty($dateString)){
            $year = substr($dateString, 0,4);
            $month = substr($dateString, 4,2);
            $day = substr($dateString, 6,2);
            $unixtimestamp = strtotime($year ."-". $month ."-". $day);
            $date->setTimestamp($unixtimestamp);
            return $date;
        }
        else {
            return null;
        }
    }


    static function personneAlreadyInArray(array $personnes, int $id):bool{
        foreach($personnes as $a){
            if($a->getId() == $id)
            {
                return true;
            }
        }
        return false;
    }


}