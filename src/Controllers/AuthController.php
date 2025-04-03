<?php
namespace App\Controllers;

use App\Models\UserModel;
use App\Utils\SecurityUtil;
use PDO;

class AuthController extends BaseController {
    private $userModel;

    public function __construct($twig, $db) {
        parent::__construct($twig, $db);
        $this->userModel = new UserModel($db);

        // Démarrer la session si elle n'est pas déjà active
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function login() {
        // Vérifier si l'utilisateur est déjà connecté via session
        if (isset($_SESSION['user_id'])) {
            // L'utilisateur est déjà connecté, rediriger en fonction du type
            $this->redirectBasedOnUserType($_SESSION['user_type']);
            return; // Important pour arrêter l'exécution
        }

        // Vérifier si un cookie persistant existe
        if (isset($_COOKIE['remember_token'])) {
            $token = $_COOKIE['remember_token'];
            $tokenHash = hash('sha256', $token);

            // Tenter de récupérer l'utilisateur correspondant au token
            $user = $this->userModel->getUserByPersistentToken($tokenHash);

            if ($user) {
                // Authentifier automatiquement l'utilisateur
                $_SESSION['user_id'] = $user['Id_Utilisateur'];
                $_SESSION['user_email'] = $user['Email_Utilisateur'];
                $_SESSION['user_type'] = $this->userModel->getUserType($user['Id_Utilisateur']);
                $_SESSION['last_activity'] = time();

                // Rafraîchir le token persistant pour la sécurité
                $this->userModel->refreshPersistentToken($user['Id_Utilisateur']);

                // Rediriger l'utilisateur vers la page appropriée
                $this->redirectBasedOnUserType($_SESSION['user_type']);
                return;
            }
        }

        // Si ce point est atteint, l'utilisateur n'est pas authentifié
        // Traiter comme à l'origine en fonction de la méthode HTTP
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            // Code existant pour afficher le formulaire de connexion
            $csrfToken = SecurityUtil::generateCsrfToken();
            echo $this->twig->render('auth/login.html.twig', [
                'csrf_token' => $csrfToken
            ]);
            return;
        }

        // Traiter le formulaire de connexion
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Vérification CSRF (code existant)
            $csrfToken = $_POST['csrf_token'] ?? '';
            if (!SecurityUtil::verifyCsrfToken($csrfToken)) {
                // Code existant inchangé
                echo $this->twig->render('auth/login.html.twig', [
                    'error' => 'Session expirée ou invalide. Veuillez réessayer.',
                    'csrf_token' => SecurityUtil::generateCsrfToken()
                ]);
                return;
            }

            // Récupérer et nettoyer les données
            $email = SecurityUtil::sanitizeInput($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $rememberMe = isset($_POST['remember_me']); // Récupérer l'état du "rester connecté"

            if (empty($email) || empty($password)) {
                // Code existant inchangé
                echo $this->twig->render('auth/login.html.twig', [
                    'error' => 'Veuillez remplir tous les champs',
                    'csrf_token' => SecurityUtil::generateCsrfToken()
                ]);
                return;
            }

            $user = $this->userModel->authenticate($email, $password);

            if (!$user) {
                // Code existant inchangé
                echo $this->twig->render('auth/login.html.twig', [
                    'error' => 'Email ou mot de passe incorrect',
                    'csrf_token' => SecurityUtil::generateCsrfToken()
                ]);
                return;
            }

            // Enregistrer les informations utilisateur dans la session
            $_SESSION['user_id'] = $user['Id_Utilisateur'];
            $_SESSION['user_email'] = $user['Email_Utilisateur'];
            $_SESSION['user_type'] = $this->userModel->getUserType($user['Id_Utilisateur']);
            $_SESSION['last_activity'] = time();

            // Si "rester connecté" est coché, configurer pour un mois
            if ($rememberMe) {
                // Définir la durée de vie du cookie de session à 30 jours (en secondes)
                $duration = 30 * 24 * 60 * 60; // 30 jours en secondes

                // Créer un token de persistance sécurisé et l'enregistrer
                $persistentToken = bin2hex(random_bytes(32));
                $tokenHash = hash('sha256', $persistentToken);
                $expiry = time() + $duration;

                // Stocker le token en base de données associé à cet utilisateur
                $this->userModel->storePersistentToken($user['Id_Utilisateur'], $tokenHash, $expiry);

                // Créer un cookie avec le token
                setcookie(
                    'remember_token',
                    $persistentToken,
                    [
                        'expires' => $expiry,
                        'path' => '/',
                        'domain' => '',
                        'secure' => isset($_SERVER['HTTPS']),
                        'httponly' => true,
                        'samesite' => 'Lax'
                    ]
                );

                // Modifier les paramètres de session pour étendre sa durée
                // Définir la durée de vie du cookie de session à 30 jours
                ini_set('session.cookie_lifetime', $duration);
                session_set_cookie_params($duration);

                // Régénérer l'ID de session pour appliquer les nouveaux paramètres
                session_regenerate_id(true);
            } else {
                // Session standard qui expire à la fermeture du navigateur
                session_regenerate_id(true);
            }

            // Rediriger l'utilisateur selon son type
            $this->redirectBasedOnUserType($_SESSION['user_type']);
        }
    }

    public function logout() {
        // Démarrer la session si elle n'est pas déjà active
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Supprimer le token persistant s'il existe
        if (isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
            $userId = $_SESSION['user_id'];
            $this->userModel->removePersistentTokens($userId);

            // Supprimer le cookie
            setcookie('remember_token', '', time() - 3600, '/');
        }

        // Détruire toutes les données de session
        $_SESSION = [];

        // Détruire le cookie de session si présent
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        // Détruire la session
        session_destroy();

        // Rediriger vers la page de connexion
        header('Location: /login');
        exit;
    }

    private function redirectBasedOnUserType($userType) {
        switch ($userType) {
            case 'admin':
                header('Location: /admin');
                break;
            case 'pilote':
                header('Location: /pilotes');
                break;
            case 'etudiant':
                header('Location: /offres');
                break;
            default:
                header('Location: /login');
                break;
        }
        exit;
    }

    public function getCurrentUser() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        // Vérifier le délai d'inactivité (30 minutes)
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
            // Session expirée
            $this->logout();
            return null;
        }

        // Mettre à jour le timestamp de dernière activité
        $_SESSION['last_activity'] = time();

        return $this->userModel->getById($_SESSION['user_id']);
    }

    public function getUserType() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        return $_SESSION['user_type'] ?? null;
    }

    /**
     * Vérifie la présence d'un token de persistance valide et authentifie automatiquement
     * l'utilisateur si c'est le cas
     */
    public function checkPersistentLogin() {
        // Déjà connecté, ne rien faire
        if (isset($_SESSION['user_id'])) {
            return;
        }

        // Vérifier si le cookie remember_token existe
        if (isset($_COOKIE['remember_token'])) {
            $token = $_COOKIE['remember_token'];
            $tokenHash = hash('sha256', $token);

            // Récupérer l'utilisateur correspondant au token
            $user = $this->userModel->getUserByPersistentToken($tokenHash);

            if ($user) {
                // Authentifier l'utilisateur
                $_SESSION['user_id'] = $user['Id_Utilisateur'];
                $_SESSION['user_email'] = $user['Email_Utilisateur'];
                $_SESSION['user_type'] = $this->userModel->getUserType($user['Id_Utilisateur']);
                $_SESSION['last_activity'] = time();

                // Régénérer le token pour augmenter la sécurité (rotation des tokens)
                $this->userModel->refreshPersistentToken($user['Id_Utilisateur']);
            }
        }
    }
}