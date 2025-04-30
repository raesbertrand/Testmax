<?php
require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use App\Bot;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$token = $_ENV['TOKEN'];
$guildApiKeys = [
    '805758165840297984' => $_ENV['TACTICUS_API_KEY_GUILDE1'],
    '1009128592388669460' => $_ENV['TACTICUS_API_KEY_GUILDE2']
];

Bot::run($token, $guildApiKeys);
