<?php

namespace Koyok\democratia\lib;

use BackedEnum;

/**
 * Interface commune pour les méthodes de l'API
 */
interface Methode extends BackedEnum {}

/**
 * Calcule le nombre de paramètres (?) dans la chaîne SQL
 */
function nombreParametre(Methode $enum): int
{
    $matches = [];

    return preg_match_all('/\?/', $enum->value, $matches);
}
