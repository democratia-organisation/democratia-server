<?php

namespace Koyok\democratia\lib;

final class KafkaOptions
{
    public string $title;

    public string $body;

    public string $topic;

    public int $nombreDOffsetPublications;

    public string $type;

    public string $token;

    public function __construct(string $title, string $body, string $topic, int $nombreDOffsetPublications, string $token, string $type)
    {
        $this->title = $title;
        $this->body = $body;
        $this->topic = $topic;
        $this->nombreDOffsetPublications = $nombreDOffsetPublications;
        $this->token = $token;
        $this->type = $type;
    }
}
