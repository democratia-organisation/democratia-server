<?php

namespace Koyok\democratia\domain\utils;

/**
 * Méthodes PATCH : Procédures de mise à jour (UPDATE)
 */
enum PatchMethode: string implements Methode
{
    case ModifInfoInternaute = 'CALL modifs_infos_internaute(?,?,?,?,?,?)'; // 6 params
    case SignalerCommentaire = 'CALL signaler_commentaire(?)';               // 1 param
    case SignalerProposition = 'CALL signaler_proposition(?)';               // 1 param
}
