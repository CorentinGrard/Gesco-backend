<?php


abstract class SessionType
{
    const NONE = "none";
    const COURS = "cours";
    const CONFERENCE = "conference";
    const TD = "td";
    const TP = "tp";
    const EXAMEN = "examen";
    const AUTRE = "autre";

    const values = [
        self::NONE,
        self::COURS,
        self::CONFERENCE,
        self::TD,
        self::TP,
        self::EXAMEN,
        self::AUTRE
    ];

}