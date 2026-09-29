<?php
// SecurityManager.php

require_once 'config.php';

class SecurityManager {
    private $window;
    private $maxAttempts;
    
    public function __construct() {
        $this->window = RATE_LIMIT_WINDOW ?? 300; // 5 minutes par défaut
        $this->maxAttempts = MAX_RATE_ATTEMPTS ?? 10;
        
        if (!isset($_SESSION['rate_limits'])) {
            $_SESSION['rate_limits'] = [];
        }
    }

    /**
     * Vérifie si une adresse IP est limitée en termes de taux de requêtes
     */
    public function isRateLimited(string $identifier): bool {
        $current = time();
        $key = "rate_limit:" . $identifier;
        
        if (!isset($_SESSION['rate_limits'][$key])) {
            $_SESSION['rate_limits'][$key] = [];
        }
        
        // Nettoyer les anciennes entrées
        $_SESSION['rate_limits'][$key] = array_filter(
            $_SESSION['rate_limits'][$key],
            function($timestamp) use ($current) {
                return $timestamp > ($current - $this->window);
            }
        );
        
        $attempts = count($_SESSION['rate_limits'][$key]);
        
        if ($attempts >= $this->maxAttempts) {
            $this->logSecurityEvent($identifier, 'rate_limit_exceeded');
            return true;
        }
        
        $_SESSION['rate_limits'][$key][] = $current;
        return false;
    }
    
    /**
     * Retourne le délai avant la prochaine tentative autorisée
     */
    public function getRetryAfter(string $identifier): int {
        $key = "rate_limit:" . $identifier;
        
        if (!isset($_SESSION['rate_limits'][$key]) || empty($_SESSION['rate_limits'][$key])) {
            return 0;
        }
        
        $oldestTime = min($_SESSION['rate_limits'][$key]);
        return max(0, ($oldestTime + $this->window) - time());
    }

    /**
     * Nettoie et sanitize les entrées
     */
    public static function sanitizeInput($input) {
        if (is_array($input)) {
            return array_map([self::class, 'sanitizeInput'], $input);
        }
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Validation du token CSRF
     */
    public static function validateCSRF() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? null;
            if (!$token || !hash_equals($_SESSION['csrf_token'], $token)) {
                $isProduction = defined('ENVIRONMENT') && ENVIRONMENT === 'production';
                $errorMessage = $isProduction ? "Erreur de validation" : "Erreur de validation CSRF";
                throw new Exception($errorMessage);
            }
        }
    }

    /**
     * Récupère le token CSRF
     */
    public static function getCSRFToken(): string {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Log les événements de sécurité
     */
    private function logSecurityEvent(string $identifier, string $event): void {
        $logEntry = sprintf(
            "[%s] Security Event: %s - IP: %s - User Agent: %s",
            date('Y-m-d H:i:s'),
            $event,
            $identifier,
            $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        );
        error_log($logEntry);
    }
}