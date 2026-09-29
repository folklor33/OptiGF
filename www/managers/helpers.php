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
