<?php
namespace App\Commands;

use Discord\Parts\Channel\Message;
use App\Utils\RaidApi;
use Carbon\Carbon;

class BombsAvailable
{
    private $guildApiKeys;

    public function __construct($guildApiKeys)
    {
        $this->guildApiKeys = $guildApiKeys;
    }

    public function handle(Message $message)
    {
        if ($message->content !== '/bombs_available') {
            return;
        }

        $guildId = $message->guild_id;
        $apiKey = $this->guildApiKeys[$guildId] ?? null;

        if (!$apiKey) {
            $message->reply("Clé API non configurée pour ce serveur.");
            return;
        }

        $data = RaidApi::fetchCurrentRaid($apiKey);
        if (!$data || !isset($data['entries'])) {
            $message->reply("❌ Données RAID inaccessibles.");
            return;
        }

        $savedNames = self::loadNameRegistry($guildId);
        $bombHistory = [];

        foreach ($data['entries'] as $e) {
            if (($e['damageType'] ?? '') === 'Bomb') {
                $uid = $e['userId'] ?? null;
                $ts = $e['completedOn'] ?? $e['startedOn'] ?? null;
                if ($uid && $ts) {
                    $bombHistory[$uid][] = $ts;
                }
            }
        }

        $bombAvailable = [];

        foreach ($savedNames as $uid => $name) {
            if (!isset($bombHistory[$uid])) {
                $bombAvailable[$uid] = $name;
                continue;
            }

            $lastUse = max($bombHistory[$uid]);
            $lastUsedAt = Carbon::createFromTimestamp($lastUse);
            if ($lastUsedAt->diffInHours(Carbon::now('UTC')) >= 18) {
                $bombAvailable[$uid] = $name;
            }
        }

        if (empty($bombAvailable)) {
            $message->reply("😕 Aucune bombe disponible pour l'instant.");
            return;
        }

        $reply = "**💣 Bombes disponibles maintenant :**\n";
        foreach ($bombAvailable as $uid => $name) {
            $reply .= "- $name\n";
        }

        $message->reply($reply);
    }

    private static function loadNameRegistry($guildId): array
    {
        $file = __DIR__ . "/../../usernames_{$guildId}.json";
        if (file_exists($file)) {
            return json_decode(file_get_contents($file), true);
        }
        return [];
    }
}
