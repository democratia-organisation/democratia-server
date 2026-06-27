<?php

namespace Tests;

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    private Client $client;

    public function get(string $url)
    {
        $this->client = new Client([
            'base_uri' => 'http://localhost:80',
            'http_errors' => false,
        ]);

        return $this->client->get($url);
    }

    public function post(string $url, array $parameters = [])
    {
        $this->client = new Client([
            'base_uri' => 'http://localhost:80',
            'http_errors' => false,
        ]);

        return $this->client->post($url, $parameters);
    }

    public function patch(string $url, array $parameters = [])
    {
        $this->client = new Client([
            'base_uri' => 'http://localhost:80',
            'http_errors' => false,
        ]);

        return $this->client->patch($url, $parameters);
    }

    public function delete(string $url)
    {
        $this->client = new Client([
            'base_uri' => 'http://localhost:80',
            'http_errors' => false,
        ]);

        return $this->client->delete($url);
    }
}
