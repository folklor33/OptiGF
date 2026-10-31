<?php
// helpers.php - Fonctions utilitaires pour le site

/**
 * Génère une URL absolue en tenant compte du BASE_PATH
 * @param string $path Le chemin relatif (ex: 'assets/img/logo.png' ou '/assets/img/logo.png')
 * @return string L'URL complète avec le BASE_PATH
 */
function url($path) {
    // Retirer le / initial s'il existe pour éviter les doubles slashes
    $path = ltrim($path, '/');
    return BASE_PATH . $path;
}

/**
 * Génère un attribut href avec le bon chemin
 * @param string $path Le chemin de destination
 * @return string L'attribut href complet
 */
function href($path) {
    return 'href="' . url($path) . '"';
}

/**
 * Génère un attribut src avec le bon chemin
 * @param string $path Le chemin de la ressource
 * @return string L'attribut src complet
 */
function src($path) {
    return 'src="' . url($path) . '"';
}


/**
 * Compare la version du Launcher OptiGF (User-Agent "OptiGF Launcher C#/v1.4") à celle attendue par le serveur
 * (CURRENT_APP_VERSION). Une version plus récente que celle du serveur n'est pas considérée comme périmée.
 * @param string $userAgent User-Agent de la requête
 * @return array ['is_launcher' => bool, 'client' => string, 'latest' => string, 'is_up_to_date' => bool, 'download_url' => string]
 */
function launcherVersionStatus(string $userAgent): array
{
    $pattern = '#/v([0-9][0-9.]*)$#';
    $latest = preg_match($pattern, (string) CURRENT_APP_VERSION, $m) ? $m[1] : (string) CURRENT_APP_VERSION;
    $isLauncher = strpos($userAgent, 'OptiGF Launcher') === 0;
    $client = preg_match($pattern, $userAgent, $c) ? $c[1] : '0';

    return [
        'is_launcher' => $isLauncher,
        'client' => $client,
        'latest' => $latest,
        'is_up_to_date' => $isLauncher && version_compare($client, $latest, '>='),
        'download_url' => 'https://optigf.eu/injection.php#download'
    ];
}
