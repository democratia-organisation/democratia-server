<?php

/**
 * Résumé de PatchMethode : enum qui stocke toutes les actions possible pour la méthode patch
 */
interface Methode extends BackedEnum  {
    
}
enum PatchMethode : string implements Methode {
    case modifNotif = "CALL modifier_config_notifs(?, ?)";
    case ModifInfoInternaute = "CALLL modifier_config_notifs(?,?,?,?,?,?)";
    case Signalercommentaire = "CALL Signalercommentaire(?)";
    case signalergroupe = "CALL signalergroupe()";
    case signalerproposition = "CALL signalerproposition()";
    case enregistrerCommentaire = "CALL enregistrerCommentaire(?, ?, ?, ?)";
    case enregistrerVote = "CALL enregistrer_vote(?, ?, ?, ?)";
    case enregistrerChoixVote = "CALL enregistrer_choix_vote(?, ?)";


}

 /**
  * Résumé de GetMethode : enum qui stocke toutes les actions possible pour la méthode get
  */
 enum GetMethode : string implements Methode  {
    case demanderVote = "CALL demanderVote(?, ?)";
    case budgetGroupe = "CALL budget_utilise_groupe(?)";
    case budgetThee = "CALL budget_utilise_theme(?,?)";
    case groupeUtilisatuer = "CALL groupes_utilisateur(?)";
    case  infoInternaute = "CALL infos_internaute(?)";
    case  budgetProposition = "CALL obtenir_budget_proposition(?)";
    case rechercherInternaute = "CALL Rechercher_internaute (?,?)";
    case  votesEffectues = "CALL votes_effectues(?,?)";  
    case sumThemesGroupe = "CALL sum_themes_groupe(?)";

 }
 /**
  * Résumé de DeleteMethode : enum qui stocke toutes les actions possible pour la méthode Delete
  */
 enum DeleteMethode : string implements Methode  {
    case supprimergroupe = "CALL supprimergroupe(?)";
    case supprimerproposition = "CALL supprimerproposition(?)";
    case Supprimerinternaute = "CALL Supprimer_internaute(?)";
    case supprimercommentaire = "CALL supprimercommentaire(?)";
}
/**
 * Résumé de PostMethode : enum qui stocke toutes les actions possible pour la méthode Post
 */
enum PostMethode : string implements Methode  {
    case ajouterProposition = "CALL ajouterProposition(?,?,?,?)";
    case AjouterMembre = "CALL Ajouter_membre(?,?)";
    case CreerGroupe = "CALL Creer_groupe(?,?,?,?,?,?)";
    case CreerUtilisateur = "CALL Creer_utilisateur(?,?,?)";
    case demarrerVote = "CALL demarrer_vote(?,?,?)";
    case reagirComm = "CALL reagirComm(?,?,?)";
    case reagircommentaire = "CALL reagircommentaire(?,?,?)";
    case reagirProp = "CALL reagirProp(?,?,?)";
}

/**
 * Résumé de nombreParametre : donne le nombre de parametre que posséde une fonction de la base de donnée
 * @param Methode $enum
 * @return int
 */
function nombreParametre(
    Methode $enum,
) : int {
    $matches = [];    
    $aDesParametre = preg_match_all('/\?/',$enum->value,$matches);
    return $aDesParametre ? count($matches[0]) : 0;
}