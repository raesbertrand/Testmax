<?php
namespace App\Commands;

use Discord\Parts\Channel\Message;
use App\Utils\RaidApi;

class RaidStatsLoop
{
    private $guildApiKeys;

    public function __construct($guildApiKeys)
    {
        $this->guildApiKeys = $guildApiKeys;
    }

    public function handle(Message $message)
    {
        if ($message->content !== '/raid_stats_loop') {
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
            $message->reply("Erreur de récupération des données.");
            return;
        }

        $legendary = array_filter($data['entries'], fn($e) => $e['tier'] >= 4);
        $tokens = $bombs = 0;

        foreach ($legendary as $e) {
            if (($e['damageType'] ?? '') === 'Bomb') $bombs++;
            else $tokens++;
        }

        $message->reply("**Stats légendaires**\nTokens: $tokens\nBombes: $bombs");
    }
}
