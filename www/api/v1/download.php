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

    // Reprise de téléchargement : "Range: bytes=DEBUT-" (ou "DEBUT-FIN")
    $start = 0;
    $end = $fileSize - 1;
    $isPartial = false;
    if (isset($_SERVER['HTTP_RANGE'])) {
        if (!preg_match('/^bytes=(\d+)-(\d*)$/', trim($_SERVER['HTTP_RANGE']), $matches)) {
            throw new Exception("En-tête Range invalide");
        }

        $start = (int) $matches[1];
        $end = $matches[2] !== '' ? min((int) $matches[2], $fileSize - 1) : $fileSize - 1;

        if ($start >= $fileSize || $start > $end) {
            header_remove();
            http_response_code(416);
            header('Content-Range: bytes */' . $fileSize);
            exit;
        }
        $isPartial = true;
    }
    $length = $end - $start + 1;

    header_remove();
    if ($isPartial) {
        http_response_code(206);
        header('Content-Range: bytes ' . $start . '-' . $end . '/' . $fileSize);
    }
    header('Accept-Ranges: bytes');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . $length);
    header('Cache-Control: no-store');

    while (ob_get_level()) {
        ob_end_clean();
    }

    $handle = fopen($filePath, 'rb');
    if (!$handle) {
        throw new Exception("Impossible d'ouvrir le fichier");
    }

    if ($start > 0) {
        fseek($handle, $start);
    }

    // Buffer optimal : 2MB pour très gros fichiers
    $bufferSize = 2097152; // 2MB
    $remaining = $length;

    while ($remaining > 0 && !feof($handle) && connection_status() == 0) {
        $buffer = fread($handle, (int) min($bufferSize, $remaining));
        if ($buffer === false || $buffer === '')
            break;

        echo $buffer;
        flush();

        $remaining -= strlen($buffer);
    }

    fclose($handle);
    exit;
}

// Fichiers autorisés pour le type "data" (chemins relatifs, sans traversal possible)
const DATA_FILE_PATTERN = '#^data/(db/[a-z0-9_]+\.ini|translate/(fr|es|pt|us)/t_[a-z0-9_]+\.ini)$#';

/**
 * Liste les .ini bruts disponibles sous <injection/variant>/data/ :
 * [chemin relatif => chemin absolu]
 */
function listDataFiles(string $root): array
{
    $dataDir = $root . DIRECTORY_SEPARATOR . 'data';
    $files = [];
    if (!is_dir($dataDir)) {
        return $files;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dataDir, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($iterator as $fileInfo) {
        if (!$fileInfo->isFile()) {
            continue;
        }
        // Les fichiers sont stockés avec leur casse d'origine (C_Item.ini, Translate/FR/...),
        // alors que pkg.idx et le launcher travaillent en minuscules : la clé du manifeste est normalisée.
        $relative = strtolower(str_replace('\\', '/', substr($fileInfo->getPathname(), strlen($root) + 1)));
        if (preg_match(DATA_FILE_PATTERN, $relative)) {
            $files[$relative] = $fileInfo->getPathname();
        }
    }
    ksort($files);
    return $files;
}

// Chemins acceptés dans data/manifest.txt (relatifs au dossier du jeu, sans remontée de dossier)
const NEW_FILE_PATTERN = '#^[a-z0-9_\-]+(/[a-z0-9_.\-]+)*\.[a-z0-9]{1,8}$#i';

/**
 * Fichiers à injecter dans les archives du jeu en plus des .ini : data/manifest.txt, un chemin relatif au dossier
 * du jeu par ligne (ex. UI\itemicon\A10436.dds). Ces fichiers sont livrés par le ZIP d'injection : l'API ne renvoie
 * que la liste, le launcher décide lui-même s'il remplace une entrée de pkg.idx ou s'il en ajoute une.
 * @return string[] chemins (séparateur "/", casse d'origine, sans doublon)
 */
function listNewFileNames(string $root): array
{
    $list = $root . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'manifest.txt';
    if (!is_file($list) || filesize($list) > 4194304) {
        return [];
    }

    $names = [];
    foreach (preg_split('/\r\n|\r|\n/', file_get_contents($list)) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || $line[0] === ';' || strpos($line, '//') === 0) {
            continue;
        }

        $name = ltrim(str_replace('\\', '/', $line), '/');
        if (strpos($name, '..') !== false || !preg_match(NEW_FILE_PATTERN, $name)) {
            error_log("manifest.txt : chemin ignoré : " . $line);
            continue;
        }
        $names[strtolower($name)] = $name;
    }
    return array_values($names);
}

/**
 * Type "data" :
 *  - sans "file" : manifeste JSON { version, files: [{ path, size, crc32, sha256 }], version_xml_b64, client_ini_b64, new_files: [chemin] }
 *    (version_xml_b64 : data/_OptiGF_Launcher_Injection.xml ; client_ini_b64 : client.ini à la racine du dossier)
 *    (new_files : contenu de data/manifest.txt, fichiers livrés par le ZIP à injecter dans les archives)
 *    (crc32 = CRC32 du contenu brut, celui que le jeu stocke dans pkg.idx)
 *  - avec "file" : contenu brut du fichier demandé (non zippé)
 */
function serveDataRequest(string $root, string $version, ?string $requestedFile): void
{
    $files = listDataFiles($root);

    if ($requestedFile === null) {
        if (empty($files)) {
            throw new Exception("Aucun fichier data disponible");
        }

        $list = [];
        foreach ($files as $relative => $absolute) {
            $list[] = [
                "path" => $relative,
                "size" => filesize($absolute),
                "crc32" => hexdec(hash_file('crc32b', $absolute)),
                "sha256" => hash_file('sha256', $absolute)
            ];
        }

        // _OptiGF_Launcher_Injection.xml : sert au launcher à savoir si la version injectée est à jour
        $versionXml = $root . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . '_OptiGF_Launcher_Injection.xml';
        $versionXmlB64 = (is_file($versionXml) && filesize($versionXml) <= 65536)
            ? base64_encode(file_get_contents($versionXml))
            : null;

        // client.ini : placé à la racine du dossier (<injection/variant>/client.ini), il remplace celui du jeu à chaque injection
        $clientIniB64 = null;
        foreach (scandir($root) as $entry) {
            $path = $root . DIRECTORY_SEPARATOR . $entry;
            if (strtolower($entry) === 'client.ini' && is_file($path) && filesize($path) <= 1048576) {
                $clientIniB64 = base64_encode(file_get_contents($path));
                break;
            }
        }

        error_log("Data manifest requested: " . count($list) . " files");
        sendJsonResponse("success", "Data manifest", [
            "version" => $version,
            "files" => $list,
            "version_xml_b64" => $versionXmlB64,
            "client_ini_b64" => $clientIniB64,
            "new_files" => listNewFileNames($root)
        ]);
    }

    // Seuls les fichiers du manifeste peuvent être téléchargés
    if (!isset($files[$requestedFile])) {
        throw new Exception("Fichier data invalide");
    }

    $absolute = realpath($files[$requestedFile]);
    if ($absolute === false || strpos($absolute, $root) !== 0) {
        throw new Exception("Chemin invalide");
    }

    streamFile($absolute, basename($absolute));
}

try {
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);
    
    if (!is_array($data) || !isset($data['token'], $data['type'], $data['variant'])
        || !is_string($data['token']) || !is_string($data['type']) || !is_string($data['variant'])) {
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

    } elseif ($data['type'] === 'injection' || $data['type'] === 'data') {
        // "data" = fichiers .ini bruts (data/db, data/translate) stockés à côté du ZIP d'injection
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

    $versionKey = $data['type'] === 'data' ? 'injection' : $data['type'];
    $version = $versionMap[$versionKey][$data['variant']] ?? null;
    if (!$version || $version === 'none') {
        throw new Exception("Aucun fichier ZIP trouvé pour ce type/variant");
    }

    if ($data['type'] === 'data') {
        $requestedFile = isset($data['file']) && is_string($data['file']) ? $data['file'] : null;
        serveDataRequest($realSourcePath, $version, $requestedFile);
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