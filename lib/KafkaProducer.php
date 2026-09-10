<?php

namespace Koyok\democratia\lib;

use Jobcloud\Kafka\Message\KafkaProducerMessage;
use Jobcloud\Kafka\Producer\KafkaProducerBuilder;
use Koyok\democratia\middleware\ServeurConfigurationMiddleware;
use RuntimeException;

final class KafkaProducer
{
    public function Produce(KafkaMessageInterface $message): void
    {
        $producer = KafkaProducerBuilder::create()
            ->withAdditionalBroker(getenv('KAFKA_URL'))
            ->build();

        $message = KafkaProducerMessage::create($message->GetTopic(), $message->GetNombreOffsetPublication())
            ->withBody(json_encode($message->GeneratePayload()));

        $producer->produce($message);

        [$isDev, $_] = ServeurConfigurationMiddleware::EnvDetermination();
        $flushDuration = $isDev == true ? 20000 : 2000;
        $result = $producer->flush($flushDuration);

        if ($result !== RD_KAFKA_RESP_ERR_NO_ERROR) {
            throw new RuntimeException("La notification n'a pas été envoyé", CodeDeRetourApi::InternalServerError->value);
        }
    }
}
