<?php
namespace App\Utils;

use GuzzleHttp\Client;

class RaidApi
{
    public static function fetchCurrentRaid($apiKey): ?array
    {
        $client = new Client();
        try {
            $response = $client->get('https://api.tacticusgame.com/api/v1/guildRaid', [
                'headers' => ['X-API-KEY' => $apiKey]
            ]);
            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            return null;
        }
    }
}
