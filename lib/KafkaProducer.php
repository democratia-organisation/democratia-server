<?php

namespace Koyok\democratia\lib;

use Jobcloud\Kafka\Message\KafkaProducerMessage;
use Jobcloud\Kafka\Producer\KafkaProducerBuilder;
use Koyok\democratia\middleware\ServeurConfiguration;
use RuntimeException;

final class KafkaProducer
{
    public function Produce(string $pushChannel, KafkaOptions $options): void
    {
        $producer = KafkaProducerBuilder::create()
            ->withAdditionalBroker(getenv('KAFKA_URL'))
            ->build();
        $payload = [
            'token' => $pushChannel,
            'title' => $options->title,
            'body' => $options->body,
            'data' => [
                'order_id' => uniqid('order_id'),
                'type' => 'promo',
            ],
        ];
        $message = KafkaProducerMessage::create($options->topic, $options->nombreDOffsetPublications)
            ->withKey('asdf-asdf-asfd-asdf')
            ->withBody(json_encode($payload))
            ->withHeaders(['key' => 'value']);

        $producer->produce($message);

        [$isDev, $isProd] = ServeurConfiguration::EnvDetermination();
        $flushDuration = $isDev == true ? 20000 : 2000;
        $result = $producer->flush($flushDuration);

        if ($result !== RD_KAFKA_RESP_ERR_NO_ERROR) {
            throw new RuntimeException("La notification n'a pas été envoyé", CodeDeRetourApi::InternalServerError->value);
        }
    }
}
