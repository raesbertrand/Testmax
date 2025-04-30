<?php
namespace App\Commands;

use Discord\Parts\Channel\Message;

class ListRegisteredNames
{
    public function handle(Message $message)
    {
        if ($message->content !== '/list_registered_names') {
            return;
        }

        $guildId = $message->guild_id;
        $registry = self::loadNameRegistry($guildId);

        if (empty($registry)) {
            $message->reply("ℹ️ Aucun nom enregistré.");
            return;
        }

        $reply = "**📄 Noms enregistrés :**\n";
        foreach ($registry as $userId => $name) {
            $reply .= "- `$userId` → **$name**\n";
        }

        // Discord limite à 2000 caractères, on tronque si besoin
        if (strlen($reply) > 1900) {
            $reply = substr($reply, 0, 1900) . "\n[...] (liste tronquée)";
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
