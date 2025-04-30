<?php
namespace App;

use Discord\Discord;
use App\Commands\RaidStatsLoop;
use App\Commands\RaidStatsByName;
use App\Commands\BombsAvailable;
use App\Commands\RegisterName;
use App\Commands\ListRegisteredNames;
use App\Commands\UnregisterName;

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

            $registerName = new RegisterName();
            $discord->on('message', fn($message) => $registerName->handle($message));

            $listRegisteredNames = new ListRegisteredNames();
            $discord->on('message', fn($message) => $listRegisteredNames->handle($message));

            $unregisterName = new UnregisterName();
            $discord->on('message', fn($message) => $unregisterName->handle($message));
        });

        $discord->run();
    }
}
