<?php

namespace Koyok\democratia\lib;

/**
 * Enumération des codes de retour HTTP standard pour l'API.
 */
enum CodeDeRetourApi: int
{
    /**
     * 100 Continue : Le serveur a reçu une partie de la requête et attend le reste.
     */
    case Continue = 100;

    /**
     * 200 OK : La requête a réussi.
     */
    case OK = 200;

    /**
     * 201 Created : La requête a abouti à la création d'une ressource.
     */
    case Created = 201;

    /**
     * 202 Accepted : La requête a été acceptée mais n'a pas encore été exécutée.
     */
    case Accepted = 202;

    /**
     * 204 No Content : La requête a réussi mais il n'y a pas de contenu à renvoyer.
     */
    case NoContent = 204;

    /**
     * 301 Redirected : La rêquete a été redirigé
     */
    case Redirected = 301;

    /**
     * 400 Bad Request : La requête est mal formée.
     */
    case BadRequest = 400;

    /**
     * 401 Unauthorized : L'authentification est nécessaire pour accéder à la ressource.
     */
    case Unauthorized = 401;

    /**
     * 403 Forbidden : L'accès à la ressource est interdit.
     */
    case Forbidden = 403;

    /**
     * 404 Not Found : La ressource demandée est introuvable.
     */
    case NotFound = 404;

    /**
     * 409 Conflict : La requête entre en conflit avec l'état actuel de la ressource.
     */
    case Conflict = 409;

    /**
     * 412 Malicious : du code malicieux a été détecté
     */
    case Malicious = 412;

    /**
     * 422 Unprocessable Entity : La requête est bien formée mais contient des erreurs de validation.
     */
    case UnprocessableEntity = 422;
    /**
     * 429 RateLimit : le nombre max de requete a été atteint
     */
    case RateLimit = 429;

    /**
     * 500 Internal Server Error : Une erreur interne s'est produite sur le serveur.
     */
    case InternalServerError = 500;

    /**
     * 503 Service Unavailable : Le serveur est temporairement indisponible.
     */
    case ServiceUnavailable = 503;
}
