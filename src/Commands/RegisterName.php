<?php
namespace App\Commands;

use Discord\Parts\Channel\Message;

class RegisterName
{
    public function handle(Message $message)
    {
        if (!preg_match('/\\/register_name\\s+(\\S+)\\s+(.+)/', $message->content, $matches)) {
            if (str_starts_with($message->content, '/register_name')) {
                $message->reply("Usage : `/register_name <userId> <nomTacticus>`");
            }
            return;
        }

        [$full, $userId, $name] = $matches;
        $guildId = $message->guild_id;

        $registry = self::loadNameRegistry($guildId);
        $registry[$userId] = $name;
        self::saveNameRegistry($guildId, $registry);

        $message->reply("✅ Nom enregistré : `$userId` → **$name**");
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
