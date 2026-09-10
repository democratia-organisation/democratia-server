<?php

namespace Koyok\democratia\lib;

use DateInterval;
use DateTimeImmutable;

final class KafkaMobileNotificationMessage implements KafkaMessageInterface
{
    public string $title;

    public string $body;

    public string $topic;

    public int $nombreDOffsetPublications = 0;

    public ?string $type = null;

    public ?string $token = null;

    public ?string $url = null;

    public ?string $authorize_token = null;

    private string $priority;

    private string $type_notification;

    public function getPriority(): string
    {
        return $this->priority;
    }

    public function getTypeNotification(): string
    {
        return $this->type_notification;
    }

    public function GetTopic(): string
    {
        return $this->topic;
    }

    public function GetNombreOffsetPublication(): int
    {
        return $this->nombreDOffsetPublications;
    }

    public function setTypeNotification(string $notification): KafkaMobileNotificationMessage
    {
        if ($notification != 'normal' && $notification != 'background') {
            throw new \Exception('Error Processing Request', 1);
        }
        $this->type_notification = $notification;

        return $this;
    }

    public function FromString(array $metadata): KafkaMobileNotificationMessage
    {
        if (! empty($metadata['url'])) {
            $this->url = $metadata['url'];
        }
        if (! empty($metadata['authorize_token'])) {
            $this->url = $metadata['authorize_token'];
        }

        return $this;
    }

    public function setUrl(string $url): KafkaMobileNotificationMessage
    {
        $this->url = $url;

        return $this;
    }

    public function setAuthorizeToken(string $authorizeToken): KafkaMobileNotificationMessage
    {
        $this->authorize_token = $authorizeToken;

        return $this;
    }

    public function setPriority(string $priority): KafkaMobileNotificationMessage
    {
        if ($priority != 'low' && $priority != 'medium' && $priority != 'high') {
            throw new \Exception('Error Processing Request', 1);
        }
        $this->priority = $priority;

        return $this;
    }

    public function setTitle(string $title): KafkaMobileNotificationMessage
    {
        $this->title = $title;

        return $this;
    }

    public function GeneratePayload(): array
    {
        $payload = [
            'token' => $this->token,
            'title' => $this->title,
            'body' => $this->body,
            'type' => $this->type,
            'data' => [
                'order_id' => uniqid('order_id'),
                'priority' => $this->getPriority(),
                'expiration' => new DateTimeImmutable('now')->add(new DateInterval('PT3M')),
                'type_notification' => $this->getTypeNotification(),
            ],
        ];
        if ($this->authorize_token != null) {
            $payload['data']['authorize_token'] = $this->authorize_token;
        }
        if ($this->url != null) {
            $payload['data']['url'] = $this->url;
        }

        return $payload;
    }

    public function setTopic(string $topic): KafkaMobileNotificationMessage
    {
        $this->topic = $topic;

        return $this;
    }

    public function setToken(string $token): KafkaMobileNotificationMessage
    {
        $this->token = $token;

        return $this;
    }

    public function setBody(string $body): KafkaMobileNotificationMessage
    {
        $this->body = $body;

        return $this;
    }

    public function setNombreDOffsetPublications(int $nombreDOffsetPublications): KafkaMobileNotificationMessage
    {
        $this->nombreDOffsetPublications = $nombreDOffsetPublications;

        return $this;
    }

    public function setType(string $type): KafkaMobileNotificationMessage
    {
        $this->type = $type;

        return $this;
    }
}
