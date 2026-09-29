<?php
// Activer l'affichage des erreurs pour le débogage
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'managers/config.php';
require_once 'managers/SecureDownloadManager.php';
require_once 'managers/SecurityManager.php';
require_once 'managers/SystemManagers.php';

SessionManager::start();

// Vérifier que c'est bien une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("ERREUR: Méthode non POST");
    $_SESSION['error_message'] = 'Méthode de requête invalide.';
    header("Location: injection.php#download");
    exit;
}

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    error_log("ERREUR: Utilisateur non connecté");
    $_SESSION['error_message'] = 'Vous devez être connecté pour télécharger.';
    header("Location: index.php#account");
    exit;
}

try {
    // Validation CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        error_log("ERREUR: Token CSRF invalide");
        throw new Exception('Token CSRF invalide. Veuillez actualiser la page.');
    }
    
    // Lancer le téléchargement
    $downloadManager = new SecureDownloadManager();
    $downloadManager->serveFile();
    
} catch (Exception $e) {
    error_log("EXCEPTION: " . $e->getMessage());
    error_log("TRACE: " . $e->getTraceAsString());
    $_SESSION['error_message'] = $e->getMessage();
    header("Location: injection.php#download");
    exit;
}