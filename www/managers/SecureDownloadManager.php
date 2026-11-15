<?php
// SecureDownloadManager.php
//
// Téléchargement PUBLIC de l'exe du launcher OptiGF : aucune connexion au site n'est demandée.
// Il sert à la fois le bouton « Télécharger » du site et la mise à jour automatique du launcher
// (api/v1/version.php publie l'URL, l'empreinte SHA-256 et la taille de ce fichier).
// Le launcher reste inutilisable sans compte OptiGF : la connexion se fait dans l'application (api/v1/connect.php).
//
// L'empreinte de référence est CURRENT_APP_SHA256 (config.php), fixée par l'admin à partir de build-protected.ps1 :
// un fichier déposé qui ne lui correspond pas n'est ni annoncé au launcher ni servi.

require_once __DIR__ . '/config.php';

class SecureDownloadManager
{
    /** URL publique de l'exe (le launcher n'accepte que des URL https://optigf.eu/...). */
    public const PUBLIC_URL = 'https://optigf.eu/download.exe.php';

    /** Chemin de l'exe courant sur le serveur, ou null s'il est absent. */
    public static function exePath(): ?string
    {
        // basename : FILENAME ne peut pas sortir du dossier exe/
        $path = DOWNLOAD_PATH . 'exe/' . basename(FILENAME);
        return is_file($path) ? $path : null;
    }

    /** Empreinte publiée par l'admin (minuscules), ou null si elle n'est pas renseignée. */
    public static function publishedSha256(): ?string
    {
        $sha = defined('CURRENT_APP_SHA256') ? strtolower(trim((string) CURRENT_APP_SHA256)) : '';
        return preg_match('/^[0-9a-f]{64}$/', $sha) ? $sha : null;
    }

    /** True si le fichier déposé correspond exactement à l'empreinte publiée. */
    private static function matchesPublished(string $path, string $published): bool
    {
        return hash_equals($published, hash_file('sha256', $path));
    }

    /**
     * Informations publiées par api/v1/version.php pour la mise à jour automatique (null si rien de fiable à proposer).
     * Le launcher vérifie la taille et l'empreinte du fichier téléchargé avant de le mettre à la place de son exe.
     */
    public static function exeInfo(): ?array
    {
        $path = self::exePath();
        $published = self::publishedSha256();
        if ($path === null || $published === null) {
            return null;
        }
        if (!self::matchesPublished($path, $published)) {
            error_log("SecureDownloadManager : " . basename(FILENAME) . " ne correspond pas à CURRENT_APP_SHA256 (mise à jour automatique désactivée)");
            return null;
        }
        return [
            'url' => self::PUBLIC_URL,
            'sha256' => $published,
            'size' => filesize($path),
        ];
    }

    public function serveFile(): void
    {
        $path = self::exePath();
        if ($path === null) {
            throw new Exception('Fichier non trouvé. Veuillez contacter les administrateurs pour signaler cette anomalie.');
        }
        $published = self::publishedSha256();
        if ($published !== null && !self::matchesPublished($path, $published)) {
            error_log("download.exe.php : " . basename(FILENAME) . " ne correspond pas à CURRENT_APP_SHA256, téléchargement refusé");
            throw new Exception('Le fichier en ligne ne correspond pas à la version publiée. Réessayez dans quelques minutes ou contactez les administrateurs.');
        }

        // Désactiver la mise en mémoire tampon
        while (ob_get_level()) {
            ob_end_clean();
        }
        set_time_limit(0);

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename(FILENAME) . '"');
        header('Content-Length: ' . filesize($path));
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        readfile($path);
        exit;
    }
}
