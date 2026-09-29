<?php
//config.php

$env = require '/usr/local/etc/php/conf.d/env.php';
date_default_timezone_set('Europe/Paris');

// Définitions de constantes à partir du fichier env
define('DB_HOST', $env['database']['host']);
define('DB_NAME', $env['database']['name']);
define('DB_USER', $env['database']['user']);
define('DB_PASS', $env['database']['pass']);

// Constantes de sécurité
define('PASSWORD_PEPPER', $env['security']['password_pepper']);
define('SESSION_TIMEOUT', $env['security']['session_timeout']);
define('MAX_LOGIN_ATTEMPTS', $env['security']['max_login_attempts']);
define('LOCKOUT_DURATION', $env['security']['lockout_duration']);
define('DOWNLOAD_COOLDOWN', $env['security']['download_cooldown']);
define('SUSPICIOUS_ATTEMPTS_THRESHOLD', $env['security']['suspicious_attempts_threshold']);
define('ADMIN_EMAIL', $env['security']['admin_email']);

define('DOWNLOAD_PATH', $env['security']['download_path']);
define('UPLOAD_PATH', $env['security']['upload_path']);
define('RATE_LIMIT_WINDOW', $env['security']['rate_limit_window']);
define('MAX_RATE_ATTEMPTS', $env['security']['max_rate_attempts']);
define('MAX_PASSWORD_LENGTH', $env['security']['max_password_length']);
define('SESSION_COOKIE_LIFETIME', $env['security']['session_cookie_lifetime']);

define('ENVIRONMENT', $env['app']['environment'] ?? 'development');

// ===== VERSIONS HARDCODÉES =====
// À mettre à jour manuellement lors de nouvelles releases

// Version de l'application (correspond au User-Agent attendu)
define('CURRENT_APP_VERSION', "OptiGF Launcher C#/v1.3");
define('FILENAME', "Launcher OptiGF v1.3.exe");

// ########## Versions des fichiers ZIP (format: AAAAMMJJxx) #############################
define('CURRENT_INJECTION_COMPILED', '2026092902');
// ########## Versions des fichiers ZIP (format: AAAAMMJJxx) #############################

define('CURRENT_INJECTION_UNCOMPILED', 'none');

define('CURRENT_UI_TRAPWORLD', '2026070701');
define('CURRENT_UI_VANILLA', '2026070701');
define('CURRENT_UI_VLIK', '2026070701');

// Détection automatique du chemin de base pour les URLs
// En production: "/" | En dev avec sous-dossier: "/www/"
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
$basePath = ($scriptName === '/' || $scriptName === '\\') ? '/' : rtrim($scriptName, '/\\') . '/';
define('BASE_PATH', $basePath);

// Inclure les fonctions helper pour les URLs
require_once __DIR__ . '/helpers.php';

// Configuration de sécurité PHP
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);

// Configuration de session sécurisée
ini_set('session.gc_maxlifetime', 3600);
session_set_cookie_params([
    'lifetime' => 3600,
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'],
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);

session_start();

// Initialiser le timestamp de dernière activité
if (!isset($_SESSION['last_activity'])) {
    $_SESSION['last_activity'] = time();
}

class HeadersManager
{
    public static function setHeadersManager()
    {
        if (headers_sent($filename, $linenum)) {
            error_log("Headers already sent in $filename on line $linenum");
            return false;
        }

        // Protection contre le clickjacking
        header("X-Frame-Options: DENY");

        // Protection contre le MIME-type sniffing
        header("X-Content-Type-Options: nosniff");

        // Forcer HTTPS
        header("Strict-Transport-Security: max-age=31536000; includeSubDomains");

        // Content Security Policy plus permissive pour les ressources locales
        $nonce = base64_encode(random_bytes(16)); // Génère un nonce aléatoire
        header(
            "Content-Security-Policy: " .
                "default-src 'self'; " .
                "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net liberapay.com ; " .
                "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net ; " .
                "img-src 'self' data: blob: liberapay.com ; " .
                "font-src 'self' data: ; " .
                "connect-src 'self'; " .
                "media-src 'self'; " .
                "object-src 'none'; " .
                "frame-src 'none'; " .
                "base-uri 'self';"
        );


        // Protection XSS supplémentaire
        header("X-XSS-Protection: 1; mode=block");

        // Contrôle des informations de référence
        header("Referrer-Policy: strict-origin-when-cross-origin");

        // Restrictions des API du navigateur
        header("Permissions-Policy: geolocation=(), microphone=(), camera=()");

        return true;
    }
}
