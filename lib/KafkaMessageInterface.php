<?php

namespace Koyok\democratia\lib;

interface KafkaMessageInterface
{
    public function GeneratePayload(): array;

    public function GetTopic(): string;

    public function GetNombreOffsetPublication(): int;
}
