<?php
// Configuration pour très gros fichiers (5GB max)
ini_set('memory_limit', '128M');
ini_set('max_execution_time', 0);
ini_set('output_buffering', 'Off');
ini_set('zlib.output_compression', 'Off');

require_once '../../managers/config.php';
require_once '../../managers/SystemManagers.php';
require_once '../../managers/UserManager.php';
require_once '../../managers/SecurityManager.php';

// Fonction pour détecter si c'est un appel API
function isApiCall(): bool
{
    // Vérifier le Content-Type de la requête (plus fiable)
    $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
    
    // Si le Content-Type contient application/json, c'est une API
    if (strpos($contentType, 'application/json') !== false) {
        return true;
    }
    
    // Vérifier aussi le Accept header
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    if (strpos($accept, 'application/json') !== false) {
        return true;
    }
    
    // Vérifier si User-Agent indique une application (OptiGF)
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if (strpos($userAgent, 'OptiGF') !== false) {
        return true;
    }
    
    return false;
}

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
        <title>OptiGF API - Download</title>
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
            <h1>API DOWNLOAD ENDPOINT</h1>
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

// Gestion des requêtes OPTIONS (CORS pre-flight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    $allowed_origins = ['https://optigf.eu', 'https://app.optigf.eu'];
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    if (in_array($origin, $allowed_origins)) {
        header('Access-Control-Allow-Origin: ' . $origin);
    }
    header('Access-Control-Allow-Methods: POST');
    header('Access-Control-Allow-Headers: Content-Type');
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

// À partir d'ici, c'est un appel API, définir les headers de sécurité
header('Content-Type: application/json; charset=UTF-8');
$allowed_origins = ['https://optigf.eu', 'https://app.optigf.eu'];
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

function streamFile($filePath, $filename)
{
    set_time_limit(0);
    ignore_user_abort(true);

    // Vérification 64-bit critique
    if (PHP_INT_SIZE < 8) {
        throw new Exception("Serveur 32-bit détecté - fichiers > 2GB non supportés");
    }

    $fileSize = filesize($filePath);
    if ($fileSize === false || $fileSize > 5368709120) { // 5GB max
        throw new Exception("Fichier trop volumineux ou inaccessible");
    }

    header_remove();
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . $fileSize);
    header('Cache-Control: no-store');
    header('Accept-Ranges: bytes');

    while (ob_get_level()) {
        ob_end_clean();
    }

    $handle = fopen($filePath, 'rb');
    if (!$handle) {
        throw new Exception("Impossible d'ouvrir le fichier");
    }

    // Buffer optimal : 2MB pour très gros fichiers
    $bufferSize = 2097152; // 2MB
    $totalSent = 0;

    while (!feof($handle) && connection_status() == 0) {
        $buffer = fread($handle, $bufferSize);
        if ($buffer === false)
            break;

        $bytesWritten = strlen($buffer);
        echo $buffer;
        flush();

        $totalSent += $bytesWritten;

        // Vérification périodique sans ralentir
        if ($totalSent % (20 * $bufferSize) === 0) { // Toutes les 40MB
            if (connection_aborted())
                break;
        }
    }

    fclose($handle);
    exit;
}

try {
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);
    
    // Debug logging
    error_log("Download request received");
    error_log("Data: " . print_r($data, true));
    error_log("Token received: " . ($data['token'] ?? 'NO TOKEN'));
    
    if (!$data || !isset($data['token']) || !isset($data['type']) || !isset($data['variant'])) {
        throw new Exception("Paramètres manquants");
    }

    // Vérifier le token
    $db = DatabaseManager::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT id, discord FROM users WHERE token = ?");
    $stmt->execute([$data['token']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception("Token invalide");
    }

    // Utiliser le chemin de configuration
    $basePath = DOWNLOAD_PATH;

    // Validation selon le type
    if ($data['type'] === 'ui') {
        $validVariants = ['vanilla', 'vlik', 'trapworld'];
        if (!in_array($data['variant'], $validVariants, true)) {
            throw new Exception("Variant UI invalide");
        }

        // Map pour UI
        $variantMap = [
            'vanilla' => 'ui/vanilla/',
            'vlik' => 'ui/vlik/',
            'trapworld' => 'ui/trapworld/',
        ];

        $sourcePath = $basePath . $variantMap[$data['variant']];

    } elseif ($data['type'] === 'injection') {
        $validVariants = ['compiled', 'uncompiled'];
        if (!in_array($data['variant'], $validVariants, true)) {
            throw new Exception("Variant injection invalide");
        }

        // Map pour injection
        $variantMap = [
            'compiled' => 'injection/compiled/',
            'uncompiled' => 'injection/uncompiled/'
        ];

        $sourcePath = $basePath . $variantMap[$data['variant']];

    } else {
        throw new Exception("Type invalide");
    }

    // Vérifier que le chemin résolu est bien dans le basePath
    $realSourcePath = realpath($sourcePath);
    $realBasePath = realpath($basePath);

    if ($realSourcePath === false || strpos($realSourcePath, $realBasePath) !== 0) {
        throw new Exception("Chemin invalide");
    }

    // Map des constantes de version selon type + variant
    $versionMap = [
        'injection' => [
            'compiled'   => trim(CURRENT_INJECTION_COMPILED),
            'uncompiled' => trim(CURRENT_INJECTION_UNCOMPILED),
        ],
        'ui' => [
            'vanilla'    => trim(CURRENT_UI_VANILLA),
            'vlik'       => trim(CURRENT_UI_VLIK),
            'trapworld'  => trim(CURRENT_UI_TRAPWORLD),
        ],
    ];

    $version = $versionMap[$data['type']][$data['variant']] ?? null;
    if (!$version || $version === 'none') {
        throw new Exception("Aucun fichier ZIP trouvé pour ce type/variant");
    }

    $zipFile = $sourcePath . $version . '.zip';

    if (!file_exists($zipFile)) {
        throw new Exception("Fichier ZIP introuvable");
    }

    // Vérifier la taille du fichier
    $fileSize = filesize($zipFile);
    if ($fileSize === false) {
        throw new Exception("Impossible de déterminer la taille du fichier");
    }

    // Log de l'activité
    error_log("Download started: User {$user['id']}, File: " . basename($zipFile) . ", Size: " . ($fileSize / 1024 / 1024) . " MB");

    // Streamer le fichier
    streamFile($zipFile, basename($zipFile));

} catch (Exception $e) {
    error_log("Download error: " . $e->getMessage());
    sendJsonResponse("error", $e->getMessage());
}
?>