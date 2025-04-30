<?php
namespace App\Commands;

use Discord\Parts\Channel\Message;
use App\Utils\RaidApi;

class RaidStatsByName
{
    private $guildApiKeys;

    public function __construct($guildApiKeys)
    {
        $this->guildApiKeys = $guildApiKeys;
    }

    public function handle(Message $message)
    {
        if (!str_starts_with($message->content, '/raid_stats_by_name')) {
            return;
        }

        preg_match('/\\/raid_stats_by_name\\s+(\\S+)/', $message->content, $matches);
        if (!isset($matches[1])) {
            $message->reply("Usage : `/raid_stats_by_name NomTacticus`");
            return;
        }

        $name = strtolower($matches[1]);
        $guildId = $message->guild_id;
        $apiKey = $this->guildApiKeys[$guildId] ?? null;

        if (!$apiKey) {
            $message->reply("Clé API non configurée pour ce serveur.");
            return;
        }

        $savedNames = self::loadNameRegistry($guildId);
        $userId = array_search($name, array_map('strtolower', $savedNames));

        if (!$userId) {
            $message->reply("❌ Nom **$name** introuvable. Utilise `/register_name` pour enregistrer un nom.");
            return;
        }

        $data = RaidApi::fetchCurrentRaid($apiKey);
        if (!$data || !isset($data['entries'])) {
            $message->reply("❌ Données RAID indisponibles.");
            return;
        }

        $entries = array_filter($data['entries'], fn($e) => ($e['userId'] ?? '') === $userId);

        $tokens = $bombs = $damage = 0;
        foreach ($entries as $e) {
            $damage += $e['damageDealt'] ?? 0;
            if (($e['damageType'] ?? '') === 'Bomb') $bombs++;
            else $tokens++;
        }

        $msg = "**Stats pour $name**\n";
        $msg .= "🎯 Tokens: $tokens\n💣 Bombes: $bombs\n💥 Dégâts: " . number_format($damage);
        $message->reply($msg);
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
