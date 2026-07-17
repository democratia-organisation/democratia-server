<?php

namespace Tests;

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Symfony\Component\Dotenv\Dotenv;

class TestCase extends BaseTestCase
{
    private Client $client;

    private string $token = '';

    public function get(string $url)
    {
        $dotenv = new Dotenv;
        $dotenv->load(dirname(__DIR__, 1).'/.env');
        $this->token = $_ENV['PUBLIC_KEY'];
        $url = $_ENV['URL'];
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
        $dotenv = new Dotenv;
        $dotenv->load(dirname(__DIR__, 1).'/.env');
        $this->token = $_ENV['PUBLIC_KEY'];
        $url = $_ENV['URL'];
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
        $dotenv = new Dotenv;
        $dotenv->load(dirname(__DIR__, 1).'/.env');
        $this->token = $_ENV['PUBLIC_KEY'];
        $url = $_ENV['URL'];
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
        $dotenv = new Dotenv;
        $dotenv->load(dirname(__DIR__, 1).'/.env');
        $this->token = $_ENV['PUBLIC_KEY'];
        $url = $_ENV['URL'];
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
