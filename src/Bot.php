<?php
namespace App;

use Discord\Discord;
use App\Commands\RaidStatsLoop;

class Bot
{
    public static function run($token, $guildApiKeys)
    {
        $discord = new Discord(['token' => $token]);

        $discord->on('ready', function ($discord) use ($guildApiKeys) {
            echo "Bot connecté en tant que {$discord->user->username}" . PHP_EOL;

            $raidStatsLoop = new RaidStatsLoop($guildApiKeys);
            $discord->on('message', fn($message) => $raidStatsLoop->handle($message));
        });

        $discord->run();
    }
}
