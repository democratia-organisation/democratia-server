<?php

namespace Koyok\democratia\domain\utils;

/**
 * Méthodes GET : Fonctions de récupération (SELECT)
 * Note : La position 0 dans vos données correspond au retour de la fonction,
 * nous ne comptons que les paramètres IN (Position >= 1).
 */
enum GetMethode: string implements Methode
{
    case budgetGroupe = 'SELECT budget_utilise_groupe(?)';              // 1 param
    case budgetTheme = 'SELECT budget_utilise_theme(?,?)';              // 2 params
    case groupeUtilisateur = 'SELECT groupes_utilisateur(?)';           // 1 param
    case infoInternaute = 'SELECT infos_internaute(?)';                 // 1 param
    case budgetProposition = 'SELECT obtenir_budget_proposition(?)';    // 1 param
    case rechercherInternaute = 'SELECT rechercher_internaute(?,?)';    // 2 params
    case votesEffectues = 'SELECT votes_effectues(?,?)';                // 2 params
    case obtenirImage = 'obtenirImage';
}
