<?php
// SecureDownloadManager.php

require_once 'config.php';
require_once 'SecurityManager.php';
require_once 'SystemManagers.php';
ini_set('memory_limit', '256M');
SessionManager::start();

class SecureDownloadManager
{
    private const ALLOWED_MIME_TYPES = ['application/exe'];

    private $db;

    public function __construct()
    {
        $this->db = DatabaseManager::getInstance()->getConnection();
    }

    public function verifyAccess()
    {
        // Vérifier si l'utilisateur est connecté et a un token valide
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['token'])) {
            throw new Exception('Accès non autorisé');
        }

        $stmt = $this->db->prepare("
        SELECT token, last_download, vip 
        FROM users 
        WHERE id = ? AND token = ? AND password IS NOT NULL
    ");
        $stmt->execute([$_SESSION['user_id'], $_SESSION['token']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Vérifier la validité du token
        if (!$user || $user['token'] !== $_SESSION['token']) {
            $errorMsg = 'Session invalide. Veuillez vous reconnecter après avoir actualisé la page.';
            SessionManager::destroy();
            header("Location: injection.php?error=" . urlencode($errorMsg) . "#download");
            exit;
        }

        // Vérifier le délai entre les téléchargements
        if ($user['last_download'] !== null) {
            $lastDownload = strtotime($user['last_download']);
            $timeRemaining = DOWNLOAD_COOLDOWN - (time() - $lastDownload);

            if ($timeRemaining > 0) {
                throw new Exception('Merci d\'attendre ' . $timeRemaining . ' secondes avant de télécharger à nouveau.');
            }
        }

        return $user['vip'] == 1;
    }

    public function serveFile()
    {
        try {
            // Vérification de la session
            if (!SessionManager::checkTimeout()) {
                throw new Exception('Session expirée');
            }

            // Déterminer le type d'utilisateur... et le fichier à servir
            $isUiUser = $this->verifyAccess();

            // Sélectionner le nom de fichier approprié

            $filename = FILENAME;


            // Construire le chemin complet
            $downloadPath = DOWNLOAD_PATH . 'exe/' . $filename;
            $contentType = 'application/exe';

            // Vérifier l'existence du fichier
            if (!file_exists($downloadPath)) {
                throw new Exception(
                    'Fichier non trouvé. Veuillez contacter les administrateurs pour signaler cette anomalie.'
                    //. $downloadPath
                );
            }

            // Mise à jour de la date du dernier téléchargement
            $stmt = $this->db->prepare("UPDATE users SET last_download = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);

            // Désactiver la mise en mémoire tampon
            if (ob_get_level()) {
                ob_end_clean();
            }

            // Paramètres critiques pour les gros fichiers
            set_time_limit(0);  // Désactive le timeout du script
            ignore_user_abort(false); // Arrête le script si l'utilisateur annule

            // En-têtes pour le téléchargement
            header('Content-Type: ' . $contentType);
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . filesize($downloadPath));
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');

            // Streaming optimisé avec contrôle de l'intégrité
            $handle = fopen($downloadPath, 'rb');
            if ($handle) {
                $bytesToRead = filesize($downloadPath);
                $bytesRead = 0;
                $bufferSize = 1024 * 1024; // 1MB chunks

                while (!feof($handle) && $bytesRead < $bytesToRead) {
                    $buffer = fread($handle, $bufferSize);
                    $bytesRead += strlen($buffer);
                    echo $buffer;
                    flush();
                }

                fclose($handle);
            } else {
                throw new Exception("Impossible d'ouvrir le fichier");
            }

            exit;
        } catch (Exception $e) {
            $_SESSION['error_message'] = $e->getMessage();
            header("Location: injection.php#download");
            exit;
        }
    }
}