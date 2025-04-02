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
        // Afficher le formulaire de connexion
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            // Générer un token CSRF
            $csrfToken = SecurityUtil::generateCsrfToken();

            echo $this->twig->render('auth/login.html.twig', [
                'csrf_token' => $csrfToken
            ]);
            return;
        }

        // Traiter le formulaire de connexion
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Vérifier le token CSRF
            $csrfToken = $_POST['csrf_token'] ?? '';
            if (!SecurityUtil::verifyCsrfToken($csrfToken)) {
                echo $this->twig->render('auth/login.html.twig', [
                    'error' => 'Session expirée ou invalide. Veuillez réessayer.',
                    'csrf_token' => SecurityUtil::generateCsrfToken()
                ]);
                return;
            }

            // Récupérer et nettoyer les données
            $email = SecurityUtil::sanitizeInput($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                echo $this->twig->render('auth/login.html.twig', [
                    'error' => 'Veuillez remplir tous les champs',
                    'csrf_token' => SecurityUtil::generateCsrfToken()
                ]);
                return;
            }

            $user = $this->userModel->authenticate($email, $password);

            if (!$user) {
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

            // Regénérer l'ID de session pour prévenir la fixation de session
            session_regenerate_id(true);

            // Déterminer le type d'utilisateur et rediriger
            $this->redirectBasedOnUserType($_SESSION['user_type']);
        }
    }

    public function logout() {
        // Démarrer la session si elle n'est pas déjà active
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
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
}