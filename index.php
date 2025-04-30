<?php
require_once __DIR__ . '/vendor/autoload.php';

use Discord\Interaction;
use Discord\InteractionResponseType;

// Renseigne ici ta clé publique (visible dans le portail développeur Discord)
$publicKey = $_ENV['DISCORD_KEY'];

// Récupération de la requête et des headers
$signature = $_SERVER['HTTP_X_SIGNATURE_ED25519'] ?? '';
$timestamp = $_SERVER['HTTP_X_SIGNATURE_TIMESTAMP'] ?? '';
$body = file_get_contents('php://input');

// Vérifie que la requête vient bien de Discord
if (!Interaction::verifyKey($body, $signature, $timestamp, $publicKey)) {
    http_response_code(401);
    echo 'Signature invalide.';
    exit;
}

// Parse le JSON de la requête
$data = json_decode($body, true);

// Si Discord envoie un PING (type 1), on répond avec un PONG (type 1)
if ($data['type'] === 1) {
    echo json_encode(['type' => InteractionResponseType::PONG]);
    exit;
}

// Ici, tu peux gérer d’autres types d’interactions comme les slash commands :
if ($data['type'] === 2) {
    // Exécution d'une commande slash
    $commandName = $data['data']['name'];

    if ($commandName === 'bonjour') {
        echo json_encode([
            'type' => InteractionResponseType::CHANNEL_MESSAGE_WITH_SOURCE,
            'data' => [
                'content' => 'Salut depuis un bot PHP 🎉 !'
            ]
        ]);
        exit;
    }
}

// Si l'interaction n'est pas gérée :
http_response_code(400);
echo 'Type d\'interaction non géré.';
