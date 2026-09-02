<?php

namespace Koyok\democratia\lib;

final class KafkaOptions
{
    public string $title;

    public string $body;

    public string $topic;

    public int $nombreDOffsetPublications = 0;

    public string $type;

    public string $token;

    public function setTitle(string $title): KafkaOptions
    {
        $this->title = $title;

        return $this;
    }

    public function setTopic(string $topic): KafkaOptions
    {
        $this->topic = $topic;

        return $this;
    }

    public function setToken(string $token): KafkaOptions
    {
        $this->token = $token;

        return $this;
    }

    public function setBody(string $body): KafkaOptions
    {
        $this->body = $body;

        return $this;
    }

    public function setNombreDOffsetPublications(int $nombreDOffsetPublications): KafkaOptions
    {
        $this->nombreDOffsetPublications = $nombreDOffsetPublications;

        return $this;
    }

    public function setType(string $type): KafkaOptions
    {
        $this->type = $type;

        return $this;
    }
}
