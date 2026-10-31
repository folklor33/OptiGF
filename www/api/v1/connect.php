<?php
// api/v1/connect.php

declare(strict_types=1);

require_once '../../managers/config.php';
require_once '../../managers/SystemManagers.php';
require_once '../../managers/UserManager.php';
require_once '../../managers/SecurityManager.php';

// Fonction pour détecter si c'est un appel API
function isApiCall(): bool
{
    // Vérifications multiples pour plus de fiabilité
    return (
        // Vérifier le Content-Type
        (isset($_SERVER['CONTENT_TYPE']) && 
         stripos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) ||
        
        // Vérifier le Accept header
        (isset($_SERVER['HTTP_ACCEPT']) && 
         stripos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) ||
        
        // Vérifier si du JSON a été envoyé
        (file_get_contents('php://input') && 
         json_decode(file_get_contents('php://input')) !== null)
    );
}

// Gestion des requêtes OPTIONS (CORS pre-flight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Methods: POST');
    header('Access-Control-Allow-Headers: Content-Type, Accept');
    exit;
}

// Vérification de la méthode HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if (isApiCall()) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            "status" => "error",
            "message" => "Méthode non autorisée"
        ]);
        exit;
    }
    showHtmlPage("Cette API n'accepte que les requêtes POST");
}

// Si ce n'est pas un appel API, afficher la page HTML
if (!isApiCall()) {
    showHtmlPage();
}

// Définition des headers JSON pour les appels API
header('Content-Type: application/json; charset=UTF-8');
$allowed_origins = ['https://optigf.eu', 'https://preprod.optigf.eu'];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowed_origins)) {
    header('Access-Control-Allow-Origin: ' . $origin);
}
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Accept');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");

// Fonction pour envoyer une réponse JSON
function sendJsonResponse(string $status, string $message, array $data = []): void
{
    if (headers_sent($filename, $linenum)) {
        error_log("Headers already sent in $filename on line $linenum");
    }

    header('Content-Type: application/json; charset=UTF-8');

    if ($status === "error") {
        sleep(random_int(1, 3)); // Protection timing attack
    }

    echo json_encode(array_merge([
        "status" => $status,
        "message" => $message
    ], $data));
    exit;
}

// Fonction pour afficher la page HTML
function showHtmlPage(?string $error = null): void
{
    header('Content-Type: text/html; charset=UTF-8');

    // Nettoyage de l'erreur pour éviter les XSS
    $error = $error ? htmlspecialchars($error, ENT_QUOTES, 'UTF-8') : null;
    ?>
    <!DOCTYPE html>
    <html lang="fr">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>OptiGF API</title>
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

            h1 {
                margin-bottom: 0.5em;
            }

            .blink {
                animation: blink 1s infinite;
            }

            .error {
                color: #f00;
                margin-top: 1em;
                max-width: 600px;
            }

            @keyframes blink {
                50% {
                    opacity: 0;
                }
            }

            .info {
                margin-top: 1em;
                font-size: 0.9em;
                color: #0f0;
            }
        </style>
    </head>

    <body>
        <div class="terminal">
            <h1>API ENDPOINT</h1>
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

// IP réelle du client (le site est derrière Cloudflare)
function getClientIp(): string
{
    return $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

// Mise à jour obligatoire : un Launcher OptiGF plus ancien que la version attendue ne peut plus se connecter.
// Les anciennes versions affichent simplement "message" ; les nouvelles exploitent aussi "update_required".
$versionStatus = launcherVersionStatus($_SERVER['HTTP_USER_AGENT'] ?? '');
if ($versionStatus['is_launcher'] && !$versionStatus['is_up_to_date']) {
    echo json_encode([
        "status" => "error",
        "message" => "Mise à jour obligatoire : votre Launcher OptiGF (v" . $versionStatus['client'] . ") n'est plus supporté. "
            . "Téléchargez la version v" . $versionStatus['latest'] . " sur " . $versionStatus['download_url'],
        "update_required" => true,
        "latest" => $versionStatus['latest'],
        "download_url" => $versionStatus['download_url']
    ]);
    exit;
}

try {
    // Vérification de la taille du payload
    if ($_SERVER['CONTENT_LENGTH'] > 1000000) { // 1MB max
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

    if (!is_array($data)) {
        throw new Exception("JSON invalide");
    }

    // Validation des champs requis (chaînes non vides ; "0" est un mot de passe valide)
    $requiredFields = ['discord', 'account_key', 'password'];
    $missingFields = array_filter($requiredFields, function ($field) use ($data) {
        return !isset($data[$field]) || !is_string($data[$field]) || $data[$field] === '';
    });

    if (!empty($missingFields)) {
        throw new Exception("Champs manquants: " . implode(', ', $missingFields));
    }

    // Validation du format des données

    if (strlen($data['discord']) > 32 || !preg_match('/^[a-zA-Z0-9_.-]{2,32}$/', $data['discord'])) {
        throw new Exception("Format Discord invalide");
    }

    if (strlen($data['account_key']) !== 24 || !ctype_alnum($data['account_key'])) {
        throw new Exception("Format de cle invalide");
    }

    if (strlen($data['password']) > MAX_PASSWORD_LENGTH) {
        throw new Exception("Mot de passe trop long");
    }

    // Nettoyage des données
    $data = array_map([SecurityManager::class, 'sanitizeInput'], $data);

    // Initialisation des managers
    $userManager = new UserManager();
    $db = DatabaseManager::getInstance()->getConnection();

    try {
        // Vérification des credentials
        $stmt = $db->prepare("SELECT id, token FROM users 
            WHERE discord = ? 
            AND account_key = ?");

        $stmt->execute([$data['discord'], $data['account_key']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            throw new Exception("Identifiants incorrects");
        }

        // Vérification du mot de passe
        $result = $userManager->login($data['discord'], $data['password'], $data['account_key']);

        if ($result) {
            // Log de la connexion réussie
            error_log(sprintf(
                "Connexion réussie - User: %d, IP: %s",
                $result['id'],
                getClientIp()
            ));

            sendJsonResponse("success", "CONNECTION SUCESS", [
                "user_id" => $result['id'],
                "discord" => $data['discord'],
                "token" => $result['token']
            ]);
        } else {
            throw new Exception("Identifiants invalides");
        }
    } catch (PDOException $e) {
        error_log("Erreur DB: " . $e->getMessage());
        throw new Exception("Erreur serveur");
    }

} catch (Throwable $e) {
    // On vérifie d'abord si la constante est définie
    $isProduction = defined('ENVIRONMENT') && ENVIRONMENT === 'production';

    $errorMessage = $isProduction ?
        "Une erreur est survenue" :
        $e->getMessage();

    error_log(sprintf(
        "Erreur de connexion - Message: %s, IP: %s",
        $e->getMessage(),
        getClientIp()
    ));

    sendJsonResponse("error", $errorMessage);
}