<?php

namespace Koyok\democratia\domain\controllers;

use Koyok\democratia\data\query\Api;
use Koyok\democratia\lib\{CodeDeRetourApi, KafkaMetaData, KafkaOptions, KafkaProducer};
use Laminas\Diactoros\Response;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};

final class CommentaireController
{
    private Api $api;

    public function __construct(Api $api)
    {
        $this->api = $api;
    }

    public function GetMessageFromProposition(ServerRequestInterface $request, array $args): array
    {
        return $this->api->execute(array_values($args), 'SELECT c.id_commentaire, contenu_message, horodatage, nb_signalement, prenom_internaute, nom_internaute, r.id_role, ifo.id_internaute FROM commentaire c INNER JOIN internaute i ON c.id_internaute = i.id_internaute INNER JOIN infos_membre ifo ON c.id_internaute = ifo.id_internaute INNER JOIN role r ON ifo.id_role = r.id_role WHERE c.id_groupe = uuid_to_bin(?,1) AND id_proposition = ?');
    }

    public function PostMessage(ServerRequestInterface $request): ResponseInterface
    {
        $response = new Response;
        $contenu = $this->api->execute(json_decode($request->getBody()->getContents(), true), 'INSERT INTO commentaire (contenu_message,horodatage,id_groupe,id_internaute,id_proposition)VALUES (?,?,?,?,?);');
        if ($contenu['sucess'] == true) {
            $broker = new KafkaProducer;
            $options = new KafkaOptions()
                ->setTitle('Democratia : Nouveau message')
                ->setBody(json_decode($request->getBody()->getContents(), true)[0])
                ->setTopic('mobile-notification-topic');
            $metadata = new KafkaMetaData()->setPriority('medium')->setTypeNotification('normal');
            $result = $this->api->execute([], 'SELECT T.device_id, T.token, T.type_device from token T INNER JOIN infos_membre ifo on T.id_internaute = ifo.id_internaute WHERE notifications & 2048 = 1');
            foreach ($result as $valeurs) {
                $options = $options->setToken($valeurs['token'])->setType($valeurs['type_device']);
                $broker->Produce($options, $metadata);
            }

            return $response->withStatus($contenu['code']);
        } else {
            throw new \Exception('Error Processing Request', CodeDeRetourApi::InternalServerError->value);
        }

    }
}
