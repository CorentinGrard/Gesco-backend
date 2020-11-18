<?php


abstract class SessionType
{
    const __default = self::NONE;
    const NONE = "none";
    const COURS = "cours";
    const CONFERENCE = "conference";
    const TD = "td";
    const TP = "tp";
    const EXAMEN = "examen";
    const AUTRE = "autre";

}