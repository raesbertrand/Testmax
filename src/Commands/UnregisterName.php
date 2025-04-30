<?php
namespace App\Commands;

use Discord\Parts\Channel\Message;

class UnregisterName
{
    public function handle(Message $message)
    {
        if (!preg_match('/\\/unregister_name\\s+(.+)/', $message->content, $matches)) {
            if (str_starts_with($message->content, '/unregister_name')) {
                $message->reply("Usage : `/unregister_name <userId|nom>`");
            }
            return;
        }

        $identifier = strtolower(trim($matches[1]));
        $guildId = $message->guild_id;

        $registry = self::loadNameRegistry($guildId);
        $removed = null;

        // D'abord on teste si c'est un userId exact
        if (isset($registry[$identifier])) {
            $removed = $registry[$identifier];
            unset($registry[$identifier]);
        } else {
            // Sinon, on cherche par nom insensible à la casse
            foreach ($registry as $uid => $name) {
                if (strtolower($name) === $identifier) {
                    $removed = $name;
                    unset($registry[$uid]);
                    break;
                }
            }
        }

        if ($removed) {
            self::saveNameRegistry($guildId, $registry);
            $message->reply("🗑️ Lien supprimé : **$removed**");
        } else {
            $message->reply("❌ Aucun nom ou ID correspondant trouvé.");
        }
    }

    private static function loadNameRegistry($guildId): array
    {
        $file = __DIR__ . "/../../usernames_{$guildId}.json";
        if (file_exists($file)) {
            return json_decode(file_get_contents($file), true);
        }
        return [];
    }

    private static function saveNameRegistry($guildId, array $data): void
    {
        $file = __DIR__ . "/../../usernames_{$guildId}.json";
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
