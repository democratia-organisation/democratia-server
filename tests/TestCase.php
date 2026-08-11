<?php

namespace Tests;

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase as BaseTestCase;

final class TestCase extends BaseTestCase
{
    private Client $client;

    private string $token = '';

    public function get(string $url)
    {
        $url = getenv('URL');
        $env = getenv('ENVIRONNEMENT');
        if ($env == 'production') {
            $url = "https://$url:443";
        } elseif ($env == 'developpment') {
            $url = "http://$url:80";
        }
        $baseArray = [
            'base_uri' => $url,
            'http_errors' => false,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ];
        if ($this->token != '') {
            $baseArray['headers']['Authorization'] = "Bearer $this->token";
        }
        $this->client = new Client($baseArray);

        return $this->client->get($url);
    }

    public function post(string $url, array $parameters = [])
    {
        $url = getenv('URL');
        $env = getenv('ENVIRONNEMENT');
        if ($env == 'production') {
            $url = "https://$url:443";
        } elseif ($env == 'developpment') {
            $url = "http://$url:80";
        }
        $baseArray = [
            'base_uri' => $url,
            'http_errors' => false,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ];
        if ($this->token != '') {
            $baseArray['headers']['Authorization'] = "Bearer: $this->token";
        }
        $this->client = new Client($baseArray);

        return $this->client->post($url, [
            'body' => json_encode($parameters),
        ]);
    }

    public function patch(string $url, array $parameters = [])
    {
        $url = getenv('URL');
        $env = getenv('ENVIRONNEMENT');
        if ($env == 'production') {
            $url = "https://$url:443";
        } elseif ($env == 'developpment') {
            $url = "http://$url:80";
        }
        $baseArray = [
            'base_uri' => $url,
            'http_errors' => false,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ];
        if ($this->token != '') {
            $baseArray['headers']['Authorization'] = "Bearer: $this->token";
        }
        $this->client = new Client($baseArray);

        return $this->client->patch($url, [
            'body' => json_encode($parameters),
        ]);
    }

    public function delete(string $url)
    {
        $url = getenv('URL');
        $env = getenv('ENVIRONNEMENT');
        if ($env == 'production') {
            $url = "https://$url:443";
        } elseif ($env == 'developpment') {
            $url = "http://$url:80";
        }
        $baseArray = [
            'base_uri' => $url,
            'http_errors' => false,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ];
        if ($this->token != '') {
            $baseArray['headers']['Authorization'] = "Bearer: $this->token";
        }
        $this->client = new Client($baseArray);

        return $this->client->delete($url);
    }
}
