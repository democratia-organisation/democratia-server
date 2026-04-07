<?php

namespace Koyok\democratia\domain\utils;

use EvTimer;
use Exception;

$w1 = new EvTimer($tempNettoyage, 0, function () {
    $verification = Bucket::NettoyerBucket();
    if (! $verification) {
        throw new Exception('Erreur inattendu', CodeDeRetourApi::InternalServerError->value);
    }
});
$w2 = new EvTimer($tempsVerifUsage, 0, function () {
    $usage = Bucket::getGlobalUsage();
    if ($usage >= Bucket::$MAXIMUM_BILLES_GLOBAL) {
        throw new Exception('Le nombre de requete maximal a été atteint', CodeDeRetourApi::InternalServerError->value);
    }
});
