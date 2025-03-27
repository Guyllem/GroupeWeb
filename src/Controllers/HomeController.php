<?php
namespace App\Controllers;

class HomeController extends BaseController {
    public function __construct($twig, $db) {
        parent::__construct($twig, $db);
    }

    public function index() {
        // Rediriger vers la page de connexion si non connecté
        $currentUser = $this->getAuth()->getCurrentUser();
        if (!$currentUser) {
            header('Location: /login');
            exit;
        }

        // Rediriger en fonction du type d'utilisateur
        $userType = $this->getAuth()->getUserType();
        switch ($userType) {
            case 'admin':
                header('Location: /admin');
                break;
            case 'pilote':
                header('Location: /pilotes');
                break;
            case 'etudiant':
                header('Location: /etudiant');
                break;
            default:
                header('Location: /login');
                break;
        }
        exit;
    }

    public function about() {
        echo $this->twig->render('home/about.html.twig');
    }

    public function mentionsLegales() {
        echo $this->twig->render('home/mentions_legales.html.twig');
    }

    public function politiqueConfidentialite() {
        echo $this->twig->render('home/politique_confidentialite.html.twig');
    }

    public function conditionsUtilisation() {
        echo $this->twig->render('home/conditions_utilisation.html.twig');
    }
}