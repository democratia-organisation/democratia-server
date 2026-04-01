<?php

namespace Koyok\democratia\src;

/**
 * Méthodes POST : Procédures de création (INSERT)
 */
enum PostMethode: string implements Methode
{
    case ajouterProposition = 'CALL ajouter_proposition(?,?,?,?,?)';    // 5 params
    case AjouterMembre = 'CALL ajouter_membre(?,?)';                     // 2 params
    case CreerGroupe = 'CALL creer_groupe(?,?,?,?,?,?)';               // 6 params
    case CreerUtilisateur = 'CALL creer_utilisateur(?,?,?,?,?)';       // 5 params
    case publierImage = 'publierImage';
}
