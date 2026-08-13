<?php

namespace Koyok\democratia\lib;

final class KafkaOptions
{
    public string $title;

    public string $body;

    public string $topic;

    public int $nombreDOffsetPublications;

    public function __construct(string $title, string $body, string $topic, int $nombreDOffsetPublications)
    {
        $this->title = $title;
        $this->body = $body;
        $this->topic = $topic;
        $this->nombreDOffsetPublications = $nombreDOffsetPublications;
    }
}
