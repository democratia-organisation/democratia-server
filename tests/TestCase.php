<?php

namespace Tests;

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected Client $client;

    public function getJson(string $url)
    {
        $this->client = new Client([
            'base_uri' => 'http://localhost:80',
            'http_errors' => false,
        ]);

        return $this->client->get($url);
    }
}
