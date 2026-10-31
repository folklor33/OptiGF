<?php
// api/v1/version.php
//
// Seul endpoint de l'API SANS authentification : le Launcher l'appelle dès son démarrage
// pour afficher sa version et savoir si une mise à jour est disponible.
// Il n'expose que la version de l'application (aucune donnée utilisateur, aucun accès BDD).

declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');
$allowed_origins = ['https://optigf.eu', 'https://preprod.optigf.eu'];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowed_origins)) {
    header('Access-Control-Allow-Origin: ' . $origin);
}
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
header("Referrer-Policy: strict-origin-when-cross-origin");

require_once '../../managers/config.php';

function sendJsonResponse(string $status, string $message, array $data = []): void
{
    echo json_encode(array_merge([
        "status" => $status,
        "message" => $message
    ], $data));
    exit;
}

// Gestion des requêtes OPTIONS (CORS pre-flight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    sendJsonResponse("error", "Cette API n'accepte que les requêtes POST");
}

$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$versionStatus = launcherVersionStatus($userAgent);

sendJsonResponse("success", "Version check successful", [
    "app" => [
        "current_version" => CURRENT_APP_VERSION,
        "latest" => $versionStatus['latest'],
        "is_up_to_date" => $versionStatus['is_up_to_date'],
        "user_version" => substr($userAgent, 0, 100)
    ],
    "download_url" => $versionStatus['download_url']
]);
