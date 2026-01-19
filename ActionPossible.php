<?php

/**
 * Interface commune pour les méthodes de l'API
 */
interface Methode extends BackedEnum {
}

/**
 * Méthodes PATCH : Procédures de mise à jour (UPDATE)
 */
enum PatchMethode : string implements Methode {
    case ModifInfoInternaute = "CALL modifs_infos_internaute(?,?,?,?,?,?)"; // 6 params
    case SignalerCommentaire = "CALL signaler_commentaire(?)";               // 1 param
    case SignalerProposition = "CALL signaler_proposition(?)";               // 1 param
}

/**
 * Méthodes GET : Fonctions de récupération (SELECT)
 * Note : La position 0 dans vos données correspond au retour de la fonction, 
 * nous ne comptons que les paramètres IN (Position >= 1).
 */
enum GetMethode : string implements Methode {
    case budgetGroupe = "SELECT budget_utilise_groupe(?)";              // 1 param
    case budgetTheme = "SELECT budget_utilise_theme(?,?)";              // 2 params
    case groupeUtilisateur = "SELECT groupes_utilisateur(?)";           // 1 param
    case infoInternaute = "SELECT infos_internaute(?)";                 // 1 param
    case budgetProposition = "SELECT obtenir_budget_proposition(?)";    // 1 param
    case rechercherInternaute = "SELECT rechercher_internaute(?,?)";    // 2 params
    case votesEffectues = "SELECT votes_effectues(?,?)";                // 2 params
}

/**
 * Méthodes DELETE : Procédures de suppression
 */
enum DeleteMethode : string implements Methode {
    case SupprimerInternaute = "CALL supprimer_internaute(?)";           // 1 param
}

/**
 * Méthodes POST : Procédures de création (INSERT)
 */
enum PostMethode : string implements Methode {
    case ajouterProposition = "CALL ajouter_proposition(?,?,?,?,?)";    // 5 params
    case AjouterMembre = "CALL ajouter_membre(?,?)";                     // 2 params
    case CreerGroupe = "CALL creer_groupe(?,?,?,?,?,?)";               // 6 params
    case CreerUtilisateur = "CALL creer_utilisateur(?,?,?,?,?)";       // 5 params
}

/**
 * Calcule le nombre de paramètres (?) dans la chaîne SQL
 */
function nombreParametre(Methode $enum): int {
    $matches = [];    
    return preg_match_all('/\?/', $enum->value, $matches);
}