<?php
namespace App\Utils;

class SecurityUtil {
    /**
     * Génère un hachage sécurisé d'un mot de passe
     * 
     * @param string $password Mot de passe en clair
     * @return string Mot de passe haché
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);
    }
    
    /**
     * Vérifie si un mot de passe correspond à un hachage
     * 
     * @param string $password Mot de passe en clair
     * @param string $hash Hachage du mot de passe
     * @return bool
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Génère un jeton CSRF sécurisé
     * 
     * @return string
     */
    public static function generateCsrfToken() {
        if (!isset($_SESSION)) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Vérifie un jeton CSRF
     * 
     * @param string $token Jeton CSRF à vérifier
     * @return bool
     */
    public static function verifyCsrfToken($token) {
        if (!isset($_SESSION)) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Génère un jeton de réinitialisation de mot de passe
     * 
     * @return string
     */
    public static function generateResetToken() {
        return bin2hex(random_bytes(32));
    }
    
    /**
     * Génère un jeton d'authentification pour l'API
     * 
     * @param int $userId ID de l'utilisateur
     * @param string $userType Type d'utilisateur
     * @param string $secretKey Clé secrète pour signer le jeton
     * @param int $expiration Durée de validité en secondes (par défaut 24h)
     * @return string
     */
    public static function generateApiToken($userId, $userType, $secretKey, $expiration = 86400) {
        $payload = [
            'sub' => $userId,
            'type' => $userType,
            'iat' => time(),
            'exp' => time() + $expiration
        ];
        
        $header = base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
        $payload = base64_encode(json_encode($payload));
        
        $signature = hash_hmac('sha256', "$header.$payload", $secretKey, true);
        $signature = base64_encode($signature);
        
        return "$header.$payload.$signature";
    }
    
    /**
     * Nettoie les entrées utilisateur
     * 
     * @param string $input Entrée à nettoyer
     * @return string
     */
    public static function sanitizeInput($input) {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }
}