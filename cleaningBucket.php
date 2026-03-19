<?php

$tempsVerifUsage = 60*5;
$tempNettoyage = 3600;
$w1 = new EvTimer($tempNettoyage,0,function (){
    $verification = Bucket::NettoyerBucket();
    if (!$verification) throw new Exception("Error Processing Request", CodeDeRetourApi::InternalServerError->value);
});
$w2 = new EvTimer($tempsVerifUsage,0,function (){
    $usage = Bucket::getGlobalUsage();
    if ($usage >= Bucket::$MAXIMUM_BILLES_GLOBAL) {
        throw new Exception("Error Processing Request", CodeDeRetourApi::InternalServerError->value);
    }
});