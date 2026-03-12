<?php

final class Bucket
{
    private int $nombreBilles;
    private int $duree;
    private static int $MAXIMUM_BILLES = 1_000_000;


    public function __construct() {
        $this->nombreBilles = 1000;
        $this->duree = 0;
    }

    public function getRatio() : float {
        
        return 0;
    }

    public function serialiser() : bool {
        return true;
    }

    public function deserialiser() : Bucket {
        return new Bucket();
    }

    private function calcul() : float {
        return 0;
    }

    private function obtenirLaDuree(int $time) : int {
        return 0;   
    }
    
}
