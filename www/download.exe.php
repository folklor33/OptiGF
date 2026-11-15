<?php
// download.exe.php
//
// Téléchargement public de l'exe du launcher : aucune connexion au site n'est requise
// (lien du site et mise à jour automatique du launcher). Le compte OptiGF reste nécessaire dans l'application.

require_once 'managers/config.php';
require_once 'managers/SecureDownloadManager.php';
require_once 'managers/SystemManagers.php';

if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST'], true)) {
    http_response_code(405);
    header('Allow: GET, POST');
    exit;
}

try {
    (new SecureDownloadManager())->serveFile();
} catch (Exception $e) {
    error_log("download.exe.php : " . $e->getMessage());

    // Le launcher (mise à jour automatique) attend un code d'erreur, pas une redirection vers une page HTML
    if (strpos($_SERVER['HTTP_USER_AGENT'] ?? '', 'OptiGF Launcher') === 0) {
        http_response_code(404);
        header('Content-Type: text/plain; charset=UTF-8');
        echo $e->getMessage();
        exit;
    }

    SessionManager::start();
    $_SESSION['error_message'] = $e->getMessage();
    header("Location: injection.php#download");
    exit;
}
