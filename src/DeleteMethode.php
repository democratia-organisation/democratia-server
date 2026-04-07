<?php

namespace Koyok\democratia\src;

/**
 * Méthodes DELETE : Procédures de suppression
 */
enum DeleteMethode: string implements Methode
{
    case SupprimerInternaute = 'CALL supprimer_internaute(?)';           // 1 param
}
