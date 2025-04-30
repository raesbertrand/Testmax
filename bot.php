<?php
// bot.php
require __DIR__ . '/vendor/autoload.php';

use Discord\Discord;
use Discord\Parts\Channel\Message;
use GuzzleHttp\Client;
use Dotenv\Dotenv;
use Carbon\Carbon;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$TOKEN = $_ENV['TOKEN'];
$GUILD_API_KEYS = [
    '805758165840297984' => $_ENV['TACTICUS_API_KEY_GUILDE1'],
    '1009128592388669460' => $_ENV['TACTICUS_API_KEY_GUILDE2']
];

$discord = new Discord([
    'token' => $TOKEN
]);

$discord->on('ready', function ($discord) use ($GUILD_API_KEYS) {
    echo "Bot connecté en tant que {$discord->user->username}" . PHP_EOL;

    $discord->on('message', function (Message $message) use ($GUILD_API_KEYS) {
        if ($message->content === '/raid_stats_loop') {
            $guildId = $message->guild_id;
            $apiKey = $GUILD_API_KEYS[$guildId] ?? null;

            if (!$apiKey) {
                $message->reply("Clé API non configurée pour ce serveur.");
                return;
            }

            $client = new Client();
            $response = $client->get('https://api.tacticusgame.com/api/v1/guildRaid', [
                'headers' => [
                    'X-API-KEY' => $apiKey
                ]
            ]);

            $data = json_decode($response->getBody(), true);

            if (!isset($data['entries'])) {
                $message->reply("Données invalides ou inaccessibles.");
                return;
            }

            $legendaryEntries = array_filter($data['entries'], function ($e) {
                return $e['tier'] >= 4;
            });

            $reply = "**Stats Raid Légendaires :**\n";
            $totals = ['tokens' => 0, 'bombs' => 0];

            foreach ($legendaryEntries as $entry) {
                $dmg = $entry['damageDealt'] ?? 0;
                $type = $entry['damageType'] ?? '';

                if ($type === 'Bomb') {
                    $totals['bombs']++;
                } else {
                    $totals['tokens']++;
                }
            }

            $reply .= "Total Tokens: {$totals['tokens']}\nTotal Bombes: {$totals['bombs']}";
            $message->reply($reply);
        }
    });
});

$discord->run();
