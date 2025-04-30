<?php
namespace App;

use Discord\Discord;
use App\Commands\RaidStatsLoop;
use App\Commands\RaidStatsByName;
use App\Commands\BombsAvailable;

class Bot
{
    public static function run($token, $guildApiKeys)
    {
        $discord = new Discord(['token' => $token]);

        $discord->on('ready', function ($discord) use ($guildApiKeys) {
            echo "Bot connecté en tant que {$discord->user->username}" . PHP_EOL;

            $raidStatsLoop = new RaidStatsLoop($guildApiKeys);
            $discord->on('message', fn($message) => $raidStatsLoop->handle($message));
       
            // Dans la fonction 'ready':
            $raidStatsByName = new RaidStatsByName($guildApiKeys);
            $discord->on('message', fn($message) => $raidStatsByName->handle($message));

            $bombsAvailable = new BombsAvailable($guildApiKeys);
            $discord->on('message', fn($message) => $bombsAvailable->handle($message));
        });

        $discord->run();
    }
}
