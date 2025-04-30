// bot.php
require __DIR__ . '/vendor/autoload.php';

use Discord\Discord;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$discord = new Discord([
    'token' => $_ENV['TOKEN']
]);

$discord->on('ready', function ($discord) {
    echo "Bot lancé !" . PHP_EOL;

    $discord->on('message', function ($message, $discord) {
        if ($message->content === '/ping') {
            $message->reply('Pong !');
        }
    });
});

$discord->run();
