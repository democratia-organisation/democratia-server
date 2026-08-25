<?php

namespace Koyok\democratia\domain\controllers;

use GuzzleHttp\Client;
use Koyok\democratia\data\query\Api;
use Psr\Http\Message\RequestInterface;

final class NotificationController
{
    private Api $api;

    public function __construct(Api $api)
    {
        $this->api = $api;
    }

    public function RegitreNotification(RequestInterface $request, array $args): array
    {
        $body = json_decode($request->getBody()->getContents(), true);
        if ($body['type_device'] == 'wns') {
            $tenantID = getenv('AZURE_LOCATION_ID');
            $appID = getenv('AZURE_APP_ID');
            $secretID = getenv('AZURE_SECRET');
            $baseArray = [
                'base_uri' => "https://login.microsoftonline.com/$tenantID/oauth2/v2.0",
                'http_errors' => false,
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Content-Length' => '0',
                ],
            ];
            $client = new Client($baseArray);
            $response = $client->post("/token?grant_type=client_credentials&client_id=$appID&client_secret=$secretID&scope=https://wns.windows.com/.default/");
        }

        return $this->api->execute([$body['type_device'], $body['token'], $args['deviceId']], 'UPDATE token SET type_device=?, token=? WHERE device_id=?');
    }

    public function DeleteNotification(RequestInterface $request, array $args)
    {
        return $this->api->execute(array_values($args), 'DELETE FROM token WHERE device_id=?');
    }

    public function EnregistrerChoix(RequestInterface $request, array $args)
    {
        $body = $request->getBody()->getContents();

        return $this->api->execute([...json_decode($body, true), ...array_values($args)], 'UPDATE infos_membre SET notifications=? WHERE id_groupe=uuid_to_bin(?,1) AND id_internaute=uuid_to_bin(?,1)');
    }
}
