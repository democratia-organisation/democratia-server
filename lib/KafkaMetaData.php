<?php

namespace Koyok\democratia\lib;

final class KafkaMetaData
{
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

    public function setTypeNotification(string $notification): KafkaMetaData
    {
        if ($notification != 'normal' && $notification != 'background') {
            throw new \Exception('Error Processing Request', 1);
        }
        $this->type_notification = $notification;

        return $this;
    }

    public function FromString(array $metadata): KafkaMetaData
    {
        if (! empty($metadata['url'])) {
            $this->url = $metadata['url'];
        }
        if (! empty($metadata['authorize_token'])) {
            $this->url = $metadata['authorize_token'];
        }

        return $this;
    }

    public function setUrl(string $url): KafkaMetaData
    {
        $this->url = $url;

        return $this;
    }

    public function setAuthorizeToken(string $authorizeToken): KafkaMetaData
    {
        $this->authorize_token = $authorizeToken;

        return $this;
    }

    public function setPriority(string $priority): KafkaMetaData
    {
        if ($priority != 'low' && $priority != 'medium' && $priority != 'high') {
            throw new \Exception('Error Processing Request', 1);
        }
        $this->priority = $priority;

        return $this;
    }
}
