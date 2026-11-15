<?php
// SecureDownloadManager.php
//
// Téléchargement PUBLIC de l'exe du launcher OptiGF : aucune connexion au site n'est demandée.
// Il sert à la fois le bouton « Télécharger » du site et la mise à jour automatique du launcher
// (api/v1/version.php publie l'URL, le SHA-256 et la taille de ce fichier).
// Le launcher reste inutilisable sans compte OptiGF : la connexion se fait dans l'application (api/v1/connect.php).

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

    /**
     * Informations publiées par api/v1/version.php pour la mise à jour automatique :
     * le launcher vérifie la taille et le SHA-256 du fichier téléchargé avant de le mettre à la place de son exe.
     */
    public static function exeInfo(): ?array
    {
        $path = self::exePath();
        if ($path === null) {
            return null;
        }
        return [
            'url' => self::PUBLIC_URL,
            'sha256' => hash_file('sha256', $path),
            'size' => filesize($path),
        ];
    }

    public function serveFile(): void
    {
        $path = self::exePath();
        if ($path === null) {
            throw new Exception('Fichier non trouvé. Veuillez contacter les administrateurs pour signaler cette anomalie.');
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
