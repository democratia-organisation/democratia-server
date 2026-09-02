<?php

namespace Koyok\democratia\domain\controllers;

use Koyok\democratia\data\query\Api;
use Koyok\democratia\lib\{CodeDeRetourApi, KafkaMetaData, KafkaOptions, KafkaProducer};
use Psr\Http\Message\ServerRequestInterface;

final class CommentaireController
{
    public function __construct(private Api $api) {}

    public function GetMessageFromProposition(ServerRequestInterface $request, array $args): array
    {
        return $this->api->execute(array_values($args), 'SELECT c.id_commentaire, contenu_message, horodatage, nb_signalement, prenom_internaute, nom_internaute, r.id_role, bin_to_uuid(ifo.id_internaute,1) AS id_internaute FROM commentaire c INNER JOIN internaute i ON c.id_internaute = i.id_internaute INNER JOIN infos_membre ifo ON c.id_internaute = ifo.id_internaute INNER JOIN role r ON ifo.id_role = r.id_role WHERE c.id_groupe = uuid_to_bin(?,1) AND id_proposition = ?');
    }

    public function PostMessage(ServerRequestInterface $request, array $args): array
    {
        $body = json_decode($request->getBody()->getContents(), true);
        $contenu = $this->api->execute([...array_values($body[0]), ...array_values($args)], 'INSERT INTO commentaire (contenu_message,horodatage,id_internaute,id_groupe,id_proposition)VALUES (?,?,uuid_to_bin(?,1),uuid_to_bin(?,1),?);');
        if ($contenu['sucess'] == true) {
            $broker = new KafkaProducer;
            $options = new KafkaOptions()
                ->setTitle('Democratia : Nouveau message')
                ->setBody($body[0]['contenuMessage'])
                ->setTopic('main-notification-topic');
            $metadata = new KafkaMetaData()->setPriority('medium')->setTypeNotification('normal');
            $resultMail = $this->api->execute([], 'SELECT personnes_a_notifier_mail(1) AS mail_concerned');
            $resultMobile = $this->api->execute([], 'SELECT personnes_a_notifier_mobile(1) AS mobile_concerned');
            if ($resultMail['data'][0]['mail_concerned'] != null) {
                $result = json_decode($resultMail['data'][0]['mail_concerned'], true);
                foreach ($result as $valeurs) {
                    $metadata = $metadata->FromString($valeurs['metadata']);
                    $options = $options
                        ->setToken($valeurs['token'])
                        ->setType($valeurs['type_device'])
                        ->setNombreDOffsetPublications(0);
                    $broker->Produce($options, $metadata);
                }
            }
            if ($resultMobile['data'][0]['mobile_concerned']) {
                $result = json_decode($resultMobile['data'][0]['mobile_concerned'], true);
                foreach ($result as $valeurs) {
                    $metadata = $metadata->FromString($valeurs['metadata']);
                    $options = $options
                        ->setToken($valeurs['token'])
                        ->setType($valeurs['type_device'])
                        ->setNombreDOffsetPublications(0);
                    $broker->Produce($options, $metadata);
                }
            }

            return $contenu;
        } else {
            throw new \Exception('Error Processing Request', CodeDeRetourApi::InternalServerError->value);
        }

    }
}
