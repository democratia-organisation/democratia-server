<?php

namespace Koyok\democratia\domain\controllers;

use GuzzleHttp\Client;
use Koyok\democratia\data\query\Api;
use Psr\Http\Message\RequestInterface;

final class NotificationController
{
    public function __construct(private Api $api) {}

    public function RegitreNotification(RequestInterface $request, array $args): array
    {
        $body = json_decode($request->getBody()->getContents(), true);
        if ($body['type_device'] == 'wns') {
            $tenantID = getenv('AZURE_LOCATION_ID');
            $appID = getenv('AZURE_APP_ID');
            $secretID = getenv('AZURE_SECRET');
            $baseArray = [
                'http_errors' => false,
            ];
            $formArray = [
                'grant_type' => 'client_credentials',
                'client_id' => $appID,
                'client_secret' => $secretID,
                'scope' => 'https://wns.windows.com/.default',
            ];
            $client = new Client($baseArray);
            $response = $client->post("https://login.microsoftonline.com/{$tenantID}/oauth2/v2.0/token", [
                'form_params' => $formArray,
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
            ]);
            $bodyWNS = json_decode($response->getBody()->getContents(), true);
            $metadata = [
                'url' => $body['token'],
            ];
            $body['token'] = $bodyWNS['access_token'] ?? null;
            if ($response->getStatusCode() > 399) {
                throw new \Exception('Error Processing Request', 1);
            }

        }

        return $this->api->execute([$body['type_device'], $body['token'], $args['deviceId'], $args['idInternaute'], json_encode($metadata)], 'INSERT INTO token (type_device,token,device_id, id_internaute, metadata) VALUES (?, ?, ?, uuid_to_bin(?,1), ?) AS new ON DUPLICATE KEY UPDATE token = new.token');
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
