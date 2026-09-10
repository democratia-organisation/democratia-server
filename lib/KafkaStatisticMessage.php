<?php

namespace Koyok\democratia\lib;

final class KafkaStatisticMessage implements KafkaMessageInterface
{
    private string $topic = 'statistique';

    public function __construct(private int $time, private string $path, private string $methode, private int $code) {}

    public function GeneratePayload(): array
    {
        return [
            'stat_id' => uniqid('stat_id'),
            'time' => $this->time,
            'method' => $this->methode,
            'request' => $this->path,
            'code' => $this->code,
        ];
    }

    public function GetTopic(): string
    {
        return $this->topic;
    }

    public function GetNombreOffsetPublication(): int
    {
        return 0;
    }
}
