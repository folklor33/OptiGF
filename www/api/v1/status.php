<?php
// api/v1/status.php

declare(strict_types=1);

// Définition des headers de sécurité
header('Content-Type: application/json; charset=UTF-8');
$allowed_origins = ['https://optigf.eu', 'https://preprod.optigf.eu'];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowed_origins)) {
    header('Access-Control-Allow-Origin: ' . $origin);
}
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");

require_once '../../managers/config.php';
require_once '../../managers/SystemManagers.php';
require_once '../../managers/SecurityManager.php';

// ===== FONCTIONS UTILITAIRES =====

/**
 * Détecte si c'est un appel API
 */
function isApiCall(): bool
{
    $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
    if (strpos($contentType, 'application/json') !== false) {
        return true;
    }
    
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    if (strpos($accept, 'application/json') !== false) {
        return true;
    }
    
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if (strpos($userAgent, 'OptiGF') !== false) {
        return true;
    }
    
    return false;
}

/**
 * Envoie une réponse JSON
 */
function sendJsonResponse(string $status, string $message, array $data = []): void
{
    if (headers_sent($filename, $linenum)) {
        error_log("Headers already sent in $filename on line $linenum");
    }

    header('Content-Type: application/json; charset=UTF-8');

    if ($status === "error") {
        sleep(random_int(1, 2));
    }

    echo json_encode(array_merge([
        "status" => $status,
        "message" => $message
    ], $data));
    exit;
}

/**
 * Affiche la page HTML pour les navigateurs
 */
function showHtmlPage(?string $error = null): void
{
    header('Content-Type: text/html; charset=UTF-8');
    $error = $error ? htmlspecialchars($error, ENT_QUOTES, 'UTF-8') : null;
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>OptiGF API - Status</title>
        <style>
            body {
                margin: 0;
                height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                font-family: monospace;
                background: #000;
                color: #0f0;
                flex-direction: column;
            }
            .terminal {
                text-align: center;
                background: rgba(0, 50, 0, 0.3);
                padding: 2em;
                border: 1px solid #0f0;
                border-radius: 4px;
                max-width: 80%;
                word-wrap: break-word;
            }
            h1 { margin-bottom: 0.5em; }
            .blink { animation: blink 1s infinite; }
            .error { color: #f00; margin-top: 1em; max-width: 600px; }
            @keyframes blink { 50% { opacity: 0; } }
            .info { margin-top: 1em; font-size: 0.9em; color: #0f0; }
        </style>
    </head>
    <body>
        <div class="terminal">
            <h1>API STATUS ENDPOINT</h1>
            <p>This is not a website<span class="blink">_</span></p>
            <?php if ($error): ?>
                <p class="error"><?php echo $error; ?></p>
            <?php endif; ?>
            <p class="info">This endpoint only accepts POST requests with JSON content-type</p>
        </div>
    </body>
    </html>
    <?php
    exit;
}

/**
 * Compare deux versions au format AAAAMMJJxx
 * Retourne true si version1 >= version2
 */
function compareVersions(string $version1, string $version2): bool
{
    return $version1 >= $version2;
}

/**
 * Vérifie si le User-Agent correspond à la version attendue de l'application
 */
function checkAppVersion(string $userAgent): bool
{
    // Le User-Agent doit se terminer exactement par la version attendue (évite 1.3 == 1.30 / 1.35)
    $expected = (string) CURRENT_APP_VERSION;
    return $expected !== '' && substr($userAgent, -strlen($expected)) === $expected;
}

/**
 * IP réelle du client (le site est derrière Cloudflare)
 */
function getClientIp(): string
{
    return $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

// ===== GESTION DES REQUÊTES =====

// Gestion des requêtes OPTIONS (CORS pre-flight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

// Vérification de la méthode HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    showHtmlPage("Cette API n'accepte que les requêtes POST");
}

// Si ce n'est pas un appel API, afficher la page HTML
if (!isApiCall()) {
    showHtmlPage();
}

// ===== TRAITEMENT DE LA REQUÊTE =====

try {
    // Vérification de la taille du payload
    if ($_SERVER['CONTENT_LENGTH'] > 100000) { // 100KB max
        throw new Exception("Payload trop grand");
    }

    // Lecture et validation du JSON
    $rawInput = file_get_contents('php://input');
    if (!$rawInput) {
        throw new Exception("Données manquantes");
    }

    $data = json_decode($rawInput, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("JSON invalide: " . json_last_error_msg());
    }

    // Vérification du token (obligatoire)
    if (!is_array($data) || !isset($data['token']) || !is_string($data['token']) || $data['token'] === '') {
        throw new Exception("Token manquant");
    }

    // Sanitize le token
    $token = SecurityManager::sanitizeInput($data['token']);

    // Vérifier le token dans la base de données
    $db = DatabaseManager::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT id, discord FROM users WHERE token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception("Token invalide");
    }

    // Récupérer le User-Agent
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    // Vérifier la version de l'application
    $appIsUpToDate = checkAppVersion($userAgent);

    // Préparer les versions installées (si fournies)
    $installedVersions = is_array($data['installed_versions'] ?? null)
        ? array_filter($data['installed_versions'], 'is_string')
        : [];

    // Construire la réponse avec les statuts de chaque composant
    $response = [
        "app" => [
            "current_version" => CURRENT_APP_VERSION,
            "is_up_to_date" => $appIsUpToDate,
            "user_version" => $userAgent
        ],
        "injection" => [
            "compiled" => [
                "current_version" => CURRENT_INJECTION_COMPILED,
                "is_up_to_date" => isset($installedVersions['injection_compiled']) 
                    ? compareVersions($installedVersions['injection_compiled'], CURRENT_INJECTION_COMPILED)
                    : null
            ],
            "uncompiled" => [
                "current_version" => CURRENT_INJECTION_UNCOMPILED,
                "is_up_to_date" => isset($installedVersions['injection_uncompiled'])
                    ? compareVersions($installedVersions['injection_uncompiled'], CURRENT_INJECTION_UNCOMPILED)
                    : null
            ]
        ],
        "ui" => [
            "vanilla" => [
                "current_version" => CURRENT_UI_VANILLA,
                "is_up_to_date" => isset($installedVersions['ui_vanilla'])
                    ? compareVersions($installedVersions['ui_vanilla'], CURRENT_UI_VANILLA)
                    : null
            ],
            "vlik" => [
                "current_version" => CURRENT_UI_VLIK,
                "is_up_to_date" => isset($installedVersions['ui_vlik'])
                    ? compareVersions($installedVersions['ui_vlik'], CURRENT_UI_VLIK)
                    : null
            ],
            "trapworld" => [
                "current_version" => CURRENT_UI_TRAPWORLD,
                "is_up_to_date" => isset($installedVersions['ui_trapworld'])
                    ? compareVersions($installedVersions['ui_trapworld'], CURRENT_UI_TRAPWORLD)
                    : null
            ]
        ]
    ];

    // Log de l'activité
    error_log(sprintf(
        "Status check - User: %d (%s), App version: %s, Up to date: %s",
        $user['id'],
        $user['discord'],
        $userAgent,
        $appIsUpToDate ? 'yes' : 'no'
    ));

    // Envoyer la réponse
    sendJsonResponse("success", "Status check successful", $response);

} catch (PDOException $e) {
    error_log("Erreur DB dans status.php: " . $e->getMessage());
    $isProduction = defined('ENVIRONMENT') && ENVIRONMENT === 'production';
    $errorMessage = $isProduction ? "Erreur serveur" : "Erreur base de données: " . $e->getMessage();
    sendJsonResponse("error", $errorMessage);
} catch (Throwable $e) {
    $isProduction = defined('ENVIRONMENT') && ENVIRONMENT === 'production';
    $errorMessage = $isProduction ? "Une erreur est survenue" : $e->getMessage();
    
    error_log(sprintf(
        "Erreur status check - Message: %s, IP: %s",
        $e->getMessage(),
        getClientIp()
    ));

    sendJsonResponse("error", $errorMessage);
}