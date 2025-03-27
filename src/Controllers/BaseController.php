<?php
namespace App\Controllers;

use PDO;

class BaseController {
    protected $twig;
    protected $db;
    protected $auth;

    public function __construct($twig, $db) {
        $this->twig = $twig;
        $this->db = $db;
        // Instancier AuthController uniquement si nécessaire, pas dans le constructeur
        // pour éviter les références circulaires
        // $this->auth = new AuthController($twig, $db);

        // Initialisez l'auth uniquement lors de son utilisation
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Ajouter des variables globales à Twig
        $this->twig->addGlobal('session', $_SESSION);
        $this->twig->addGlobal('current_url', $_SERVER['REQUEST_URI']);
    }

// Méthode getter pour auth
    protected function getAuth() {
        if (!isset($this->auth)) {
            $this->auth = new AuthController($this->twig, $this->db);
        }
        return $this->auth;
    }

    protected function requireAuth() {
        $currentUser = $this->getAuth()->getCurrentUser();

        if (!$currentUser) {
            // Stocker l'URL demandée pour redirection après connexion
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            header('Location: /login');
            exit;
        }
    }

    protected function requireAdmin() {
        $currentUser = $this->getAuth()->getCurrentUser();
        $userType = $this->getAuth()->getUserType();

        if (!$currentUser) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            header('Location: /login');
            exit;
        }

        if ($userType !== 'admin') {
            // Accès non autorisé
            header('HTTP/1.1 403 Forbidden');
            echo $this->twig->render('error.html.twig', [
                'code' => 403,
                'message' => 'Accès interdit. Vous devez être administrateur pour accéder à cette page.'
            ]);
            exit;
        }
    }

    protected function requirePilote() {
        $currentUser = $this->getAuth()->getCurrentUser();
        $userType = $this->getAuth()->getUserType();

        if (!$currentUser) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            header('Location: /login');
            exit;
        }

        if ($userType !== 'pilote' && $userType !== 'admin') {
            // Accès non autorisé
            header('HTTP/1.1 403 Forbidden');
            echo $this->twig->render('error.html.twig', [
                'code' => 403,
                'message' => 'Accès interdit. Vous devez être pilote ou administrateur pour accéder à cette page.'
            ]);
            exit;
        }
    }

    protected function requireEtudiant() {
        $currentUser = $this->getAuth()->getCurrentUser();
        $userType = $this->getAuth()->getUserType();

        if (!$currentUser) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            header('Location: /login');
            exit;
        }

        if ($userType !== 'etudiant') {
            // Accès non autorisé
            header('HTTP/1.1 403 Forbidden');
            echo $this->twig->render('error.html.twig', [
                'code' => 403,
                'message' => 'Accès interdit. Vous devez être étudiant pour accéder à cette page.'
            ]);
            exit;
        }
    }

    /**
     * Ajoute un message flash dans la session
     *
     * @param string $type Type de message (success, error, info, warning)
     * @param string $message Contenu du message
     */
    protected function addFlashMessage($type, $message) {
        if (!isset($_SESSION['flash_messages'])) {
            $_SESSION['flash_messages'] = [];
        }

        $_SESSION['flash_messages'][] = [
            'type' => $type,
            'message' => $message
        ];
    }

    /**
     * Récupère et efface les messages flash
     *
     * @return array Messages flash
     */
    protected function getFlashMessages() {
        $messages = $_SESSION['flash_messages'] ?? [];
        unset($_SESSION['flash_messages']);

        return $messages;
    }
}