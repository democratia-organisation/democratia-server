<?php

namespace Koyok\democratia\lib;

final class KafkaMetaData
{
    public ?string $url;

    public ?string $authorize_token;

    private string $priority;

    private string $type_notification;

    public function __construct(string $url, string $authorize_token, string $priority, string $type_notification)
    {
        if ($type_notification != 'normal' && $type_notification != 'background') {
            throw new \Exception('Error Processing Request', 1);
        }
        if ($priority != 'low' && $priority != 'medium' && $priority != 'high') {
            throw new \Exception('Error Processing Request', 1);
        }
        $this->url = $url;
        $this->authorize_token = $authorize_token;
        $this->priority = $priority;
        $this->type_notification = $type_notification;
    }

    public function getPriority(): string
    {
        return $this->priority;
    }

    public function getTypeNotification(): string
    {
        return $this->type_notification;
    }

    public function setTypeNotification(string $notification): void
    {
        if ($notification != 'normal' && $notification != 'background') {
            throw new \Exception('Error Processing Request', 1);
        }
        $this->type_notification = $notification;
    }

    public function setPriority(string $priority): void
    {
        if ($priority != 'low' && $priority != 'medium' && $priority != 'high') {
            throw new \Exception('Error Processing Request', 1);
        }
        $this->priority = $priority;
    }
}
