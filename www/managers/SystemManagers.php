<?php
// SystemManagers.php

require_once 'config.php';

class DatabaseManager {
    private $pdo;
    private static $instance = null;

    private function __construct() {
        try {
            $this->pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER, 
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_TIMEOUT => 5 // Timeout en secondes
                ]
            );
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw new Exception("Connexion à la base de données impossible.");
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }
}

class SessionManager {
    public static function start() {
        if (session_status() === PHP_SESSION_ACTIVE && empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    public static function regenerate() {
        session_regenerate_id(true);
    }

    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    public static function get($key) {
        return $_SESSION[$key] ?? null;
    }
    
    public static function destroy() {
        session_unset();
        session_destroy();
        setcookie(session_name(), '', time() - 3600, '/');
    }

    public static function checkTimeout() {
        if (isset($_SESSION['last_activity'])) {
            $inactive = time() - $_SESSION['last_activity'];
            if ($inactive > SESSION_TIMEOUT) {
                self::destroy();
                return false;
            }
        }
        $_SESSION['last_activity'] = time();
        return true;
    }
}
