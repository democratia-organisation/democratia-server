<?php

namespace Koyok\democratia\domain\controllers;

use Koyok\democratia\data\query\Api;
use Koyok\democratia\lib\{DeleteMethode, PatchMethode, PostMethode};
use Psr\Http\Message\ServerRequestInterface;

// TODO : pour implémenter le query route
/**
 * [<queryPart>] => [
 * "<Method>" => [
 * "{{:}<path>}"  => [{namedParameters}, {dataParameters}  ,"<sql request>", ]
 * ]
 * ]
 */
final class InternauteController
{
    private Api $api;

    public function __construct(Api $api)
    {
        $this->api = $api;
    }

    public function GetGroupe(ServerRequestInterface $request, array $args): array
    {
        // tableau recré afin d'avoir une clé qui soit 0 et non idInternaute
        return $this->api->execute([$args['idInternaute']], 'SELECT BIN_TO_UUID(g.id_groupe) AS id_groupe, nom_groupe, budget, couleur_groupe, image, nb_signalement, nbj_dft_discuss, nbj_dft_vote FROM groupe g INNER JOIN infos_membre ifo ON g.id_groupe = ifo.id_groupe WHERE id_internaute=uuid_to_bin(?,1)');
    }

    public function GetMailDoublon(ServerRequestInterface $request, array $args): array
    {
        return $this->api->execute([$args['email']], 'SELECT COUNT(courriel) FROM internaute WHERE courriel=?');
    }

    public function Login(ServerRequestInterface $request): array
    {
        return $this->api->execute(json_decode($request->getBody()->getContents(), true), 'SELECT nom_internaute, prenom_internaute, adresse_postale, courriel, bin_to_uuid(id_internaute, 1) as id_internaute, hashageMDP FROM internaute WHERE courriel=? AND hashageMDP=?');
    }

    public function SupprimerInternaute(ServerRequestInterface $request, array $args): array
    {
        $requete = $this->api->tryGetAction('SupprimerInternaute', DeleteMethode::class);

        return $this->api->execute([$args['idInternaute']], $requete);
    }

    public function GetInternaute(ServerRequestInterface $request, array $args): array
    {
        return $this->api->execute([$args['idInternaute']], 'SELECT prenom_internaute, adresse_postale, courriel, bin_to_uuid(id_internaute, 1) as id_internaute, hashageMDP FROM internaute WHERE id_internaute=uuid_to_bin(?,1)');
    }

    public function CreerInternaute(ServerRequestInterface $request): array
    {
        $requete = $this->api->tryGetAction('CreerUtilisateur', PostMethode::class);

        return $this->api->execute(json_decode($request->getBody()->getContents(), true), $requete);
    }

    public function ModifierInternaute(ServerRequestInterface $request): array
    {
        $requete = $this->api->tryGetAction('ModifInfoInternaute', PatchMethode::class);

        return $this->api->execute(json_decode($request->getBody()->getContents(), true), $requete);
    }
}
