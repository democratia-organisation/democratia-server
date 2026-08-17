<?php

namespace Koyok\democratia\lib;

use DateInterval;
use DateTimeImmutable;
use Jobcloud\Kafka\Message\KafkaProducerMessage;
use Jobcloud\Kafka\Producer\KafkaProducerBuilder;
use Koyok\democratia\middleware\ServeurConfiguration;
use RuntimeException;

final class KafkaProducer
{
    public function Produce(KafkaOptions $options, KafkaMetaData $metadata): void
    {
        $producer = KafkaProducerBuilder::create()
            ->withAdditionalBroker(getenv('KAFKA_URL'))
            ->build();
        $payload = [
            'token' => $options->token,
            'title' => $options->title,
            'body' => $options->body,
            'data' => [
                'order_id' => uniqid('order_id'),
                'priority' => $metadata->getPriority(),
                'expiration' => new DateTimeImmutable('now')->add(new DateInterval('PT3M')),
                'type_notification' => $metadata->getTypeNotification(),
            ],
        ];
        if ($metadata->authorize_token != null) {
            $payload['data']['authorize_token'] = $metadata->authorize_token;
        }
        if ($metadata->url != null) {
            $payload['data']['url'] = $metadata->url;
        }
        $message = KafkaProducerMessage::create($options->topic, $options->nombreDOffsetPublications)
            ->withBody(json_encode($payload));

        $producer->produce($message);

        [$isDev, $isProd] = ServeurConfiguration::EnvDetermination();
        $flushDuration = $isDev == true ? 20000 : 2000;
        $result = $producer->flush($flushDuration);

        if ($result !== RD_KAFKA_RESP_ERR_NO_ERROR) {
            throw new RuntimeException("La notification n'a pas été envoyé", CodeDeRetourApi::InternalServerError->value);
        }
    }
}
