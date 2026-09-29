<?php
// UserManager.php

require_once 'config.php';
require_once 'SystemManagers.php';
require_once 'SecurityManager.php';



class UserManager
{
    private $db;
    private $security;

    public function __construct()
    {
        $this->db = DatabaseManager::getInstance()->getConnection();
        $this->security = new SecurityManager();
    }

    // Validation Discord
    private function isValidDiscord($discord)
    {
        return preg_match('/^[a-zA-Z0-9_.-]{2,32}$/', $discord) === 1;
    }

    // Hash de mot de passe sécurisé
    private function hashPassword($password)
    {
        if (strlen($password) < 8 || strlen($password) > MAX_PASSWORD_LENGTH) {
            throw new Exception("Le mot de passe doit contenir entre 8 et " . MAX_PASSWORD_LENGTH . " caractères.");
        }

        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/', $password)) {
            throw new Exception("Le mot de passe doit contenir au moins une majuscule, une minuscule et un chiffre.");
        }

        $hashedPassword = hash_hmac('sha256', $password, PASSWORD_PEPPER);
        return password_hash($hashedPassword, PASSWORD_ARGON2ID);
    }

    // Vérification du mot de passe
    private function verifyPassword($inputPassword, $storedHash)
    {
        $hashedInput = hash_hmac('sha256', $inputPassword, PASSWORD_PEPPER);
        return password_verify($hashedInput, $storedHash);
    }

    private function getRealIPAddress()
    {
        $ip = null;

        // Liste des en-têtes possibles avec SWAG
        $headers = [
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                // Pour X-Forwarded-For, qui peut contenir plusieurs IPs
                $ips = explode(',', $_SERVER[$header]);
                $ip = trim($ips[0]);

                // Validation stricte de l'IP
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return '0.0.0.0';  // Défaut si aucune IP valide trouvée
    }

    private function logActivity($userId, $action, $details = '')
    {
        // Récupérer le discord associé à cet utilisateur
        $stmtUser = $this->db->prepare("SELECT discord FROM users WHERE id = ?");
        $stmtUser->execute([$userId]);
        $userData = $stmtUser->fetch(PDO::FETCH_ASSOC);
        $discord = $userData['discord'] ?? null;

        // Récupérer l'IP réelle
        $ip = $this->getRealIPAddress();

        $stmt = $this->db->prepare("
            INSERT INTO activity_logs (user_id, discord, action, ip_address, user_agent, details) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $userId,
            $discord,
            $action,
            $ip,
            $_SERVER['HTTP_USER_AGENT'],
            $details
        ]);
    }

    public function register($accountKey, $discord, $password)
    {
        // Rate limiting
        if ($this->security->isRateLimited($_SERVER['REMOTE_ADDR'])) {
            $retryAfter = $this->security->getRetryAfter($_SERVER['REMOTE_ADDR']);
            sleep(random_int(1, 3));
            throw new Exception("Trop de tentatives. Réessayez dans {$retryAfter} secondes.");
        }

        // Sanitize inputs
        $accountKey = SecurityManager::sanitizeInput($accountKey);
        $discord = SecurityManager::sanitizeInput($discord);

        if (!$this->isValidDiscord($discord)) {
            sleep(random_int(1, 3));
            throw new Exception("Identifiant Discord invalide.");
        }

        // Nettoyer les entrées
        $accountKey = trim($accountKey);
        $discord = trim($discord);

        // Vérifier la clé de téléchargement et le couple discord/clé
        $stmt = $this->db->prepare("
            SELECT id, date, last_download, account_key, discord, token, password
            FROM users 
            WHERE account_key = ?
            AND discord = ?
        ");

        $stmt->execute([$accountKey, $discord]);

        // Vérifier les erreurs SQL potentielles
        $errorInfo = $stmt->errorInfo();
        if ($errorInfo[0] !== '00000') {
            error_log("SQL Error: " . print_r($errorInfo, true));
            sleep(random_int(1, 3));
            throw new Exception("Erreur SQL : " . $errorInfo[2]);
        }

        $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$existingUser) {
            sleep(random_int(1, 3));
            throw new Exception("Combinaison 'Clé / Discord' invalide. Pensez à vérifier vos MP Discord (Carso) contenant votre Clé de Carso à jour.");
        }

        // Préparer les mises à jour
        $updates = [];
        $updateValues = [];
        $hasUpdate = false;

        // Vérification et mise à jour du mot de passe
        $hashedPassword = $this->hashPassword($password);

        // Si le mot de passe est NULL ou différent, mettre à jour
        if ($existingUser['password'] === NULL || !$this->verifyPassword($password, $existingUser['password'])) {
            $updates[] = "password = ?";
            $updateValues[] = $hashedPassword;
            $hasUpdate = true;
        }

        // Si aucune mise à jour, lever une exception
        if (!$hasUpdate) {
            return [
                'warning' => "Votre compte utilise déjà ce mot de passe. Aucune modification n'a été effectuée."
            ];
        }

        // Ajouter la date de mise à jour
        $updates[] = "date = CURRENT_TIMESTAMP";

        // Préparer la requête de mise à jour
        $updateQuery = "UPDATE users SET " . implode(", ", $updates) . " 
                        WHERE id = ? AND account_key = ? AND discord = ?";
        $updateValues[] = $existingUser['id'];
        $updateValues[] = $accountKey;
        $updateValues[] = $existingUser['discord'];

        // Déterminer si le mot de passe a changé AVANT l'exécution
        $passwordChanged = $existingUser['password'] === NULL || !$this->verifyPassword($password, $existingUser['password']);

        // Exécuter la mise à jour
        $stmt = $this->db->prepare($updateQuery);
        $result = $stmt->execute($updateValues);

        if (!$result) {
            sleep(random_int(1, 3));
            throw new Exception("Échec de la mise à jour du compte. La requête n'a pas abouti.");
        }

        $this->logActivity(
            $existingUser['id'],
            'register',
            $passwordChanged ? 'Password Changed' : ''
        );

        return [
            'password_changed' => $passwordChanged
        ];
    }

    public function login($discord, $password, $accountKey)
    {
        $ip = $this->getRealIPAddress();

        // Détecter les patterns suspects
        $stmt = $this->db->prepare("
        SELECT COUNT(DISTINCT discord) as unique_attempts 
        FROM activity_logs 
        WHERE ip_address = ? 
        AND action = 'failed_login' 
        AND created_at > DATE_SUB(NOW(), INTERVAL 10 MINUTE)
    ");
        $stmt->execute([$ip]);
        $suspicious = $stmt->fetch();

        if ($suspicious['unique_attempts'] > SUSPICIOUS_ATTEMPTS_THRESHOLD) {
            error_log("SECURITY ALERT: IP $ip attempting multiple accounts");
            mail(ADMIN_EMAIL, 'Security Alert', "Suspicious activity from IP: $ip");
            sleep(LOCKOUT_DURATION);
        }
        // Initialiser le délai aléatoire dès le début de la fonction
        $randomDelay = random_int(0, 1);

        try {
            // Rate limiting
            if ($this->security->isRateLimited($_SERVER['REMOTE_ADDR'])) {
                $retryAfter = $this->security->getRetryAfter($_SERVER['REMOTE_ADDR']);
                throw new Exception("Trop de tentatives. Réessayez dans {$retryAfter} secondes.");
            }

            $discord = SecurityManager::sanitizeInput($discord);
            $accountKey = SecurityManager::sanitizeInput($accountKey);

            $stmt = $this->db->prepare("
            SELECT id, date, last_download, account_key, discord, token, password 
            FROM users 
            WHERE discord = ? AND account_key = ?");
            $stmt->execute([$discord, $accountKey]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                error_log("Failed login attempt for discord: " . $discord);
                sleep($randomDelay);
                throw new Exception("Combinaison 'Clé / Discord' invalide. Veuillez réessayer.");
            }

            if (!$user['password']) {
                sleep($randomDelay);
                throw new Exception("Mot de passe non défini. Veuillez configurer votre compte.");
            }

            if (!$this->verifyPassword($password, $user['password'])) {
                $this->logActivity($user['id'], 'failed_login');
                sleep($randomDelay);
                throw new Exception("Mot de passe incorrect. Veuillez réessayer.");
            }

            // Régénérer le token de session
            $newToken = bin2hex(random_bytes(32));
            $updateStmt = $this->db->prepare("UPDATE users SET token = ? WHERE id = ?");
            $updateStmt->execute([$newToken, $user['id']]);

            $this->logActivity($user['id'], 'successful_login');

            // CORRECTION : Régénérer l'ID de session pour éviter la fixation
            SessionManager::regenerate();

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['token'] = $newToken;
            $_SESSION['is_logged_in'] = true;

            // Appliquer le délai même en cas de succès
            sleep($randomDelay);

            return [
                'id' => $user['id'],
                'discord' => $user['discord'],
                'token' => $newToken
            ];
        } catch (Exception $e) {
            // Assurer un délai constant même en cas d'erreur
            sleep($randomDelay);
            throw $e;
        }
    }
}