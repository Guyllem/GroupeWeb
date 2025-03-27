<?php
/**
 * Configuration des sessions
 * 
 * Ce fichier doit être inclus au début de l'application pour configurer
 * les paramètres de sécurité des sessions PHP.
 */

// Définir le chemin pour stocker les sessions
$sessionPath = __DIR__ . '/../var/sessions';

// Créer le répertoire s'il n'existe pas
if (!file_exists($sessionPath)) {
    mkdir($sessionPath, 0700, true);
}

// Configurer la gestion des sessions
ini_set('session.use_strict_mode', 1);
ini_set('session.use_cookies', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.use_trans_sid', 0);
ini_set('session.cache_limiter', 'nocache');

// Configurer les cookies de session
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0); // Activer en HTTPS uniquement
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.cookie_lifetime', 0); // Expiration à la fermeture du navigateur

// Configurer le stockage des sessions
ini_set('session.save_path', $sessionPath);
ini_set('session.gc_maxlifetime', 1800); // 30 minutes
ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor', 100);

// Configurer le nom de la session
session_name('CESI_SESSION');

// Définir une fonction de validation de l'ID de session
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax'
]);

// Fonction pour nettoyer les anciennes sessions
function cleanOldSessions() {
    global $sessionPath;
    
    $maxLifetime = ini_get('session.gc_maxlifetime');
    $now = time();
    
    foreach (glob("{$sessionPath}/sess_*") as $file) {
        if (is_file($file) && ($now - filemtime($file) > $maxLifetime)) {
            @unlink($file);
        }
    }
}

// Exécuter le nettoyage avec une probabilité de 1%
if (mt_rand(1, 100) === 1) {
    cleanOldSessions();
}
