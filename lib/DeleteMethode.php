<?php

namespace Koyok\democratia\lib;

/**
 * Méthodes DELETE : Procédures de suppression
 */
enum DeleteMethode: string implements Methode
{
    case SupprimerInternaute = 'CALL supprimer_internaute(?)';           // 1 param
}
