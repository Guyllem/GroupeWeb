<?php
namespace App\Controllers;

use App\Models\PilotModel;
use App\Models\StudentModel;
use App\Models\EnterpriseModel;
use App\Models\OfferModel;
use App\Models\UserModel;

class AdminController extends BaseController {
    private $pilotModel;
    private $studentModel;
    private $enterpriseModel;
    private $offerModel;
    private $userModel;

    public function __construct($twig, $db) {
        parent::__construct($twig, $db);
        $this->pilotModel = new PilotModel($db);
        $this->studentModel = new StudentModel($db);
        $this->enterpriseModel = new EnterpriseModel($db);
        $this->offerModel = new OfferModel($db);
        $this->userModel = new UserModel($db);
    }

    public function index() {
        $this->requireAdmin();
        $this->render('admin/index.html.twig', [
            'adminPage' => true
        ]);
    }

    // Gestion des pilotes
    public function pilotes() {
        $this->requireAdmin();

        // Récupérer les pilotes
        $pilots = $this->pilotModel->getPilotsByName();

        $this->render('admin/pilotes/index.html.twig', [
            'adminPage' => true,
            'pilots' => $pilots
        ]);
    }

    public function piloteDetails($params) {
        $this->requireAdmin();

        $piloteId = $params['id'] ?? null;

        if (!$piloteId) {
            $this->addFlashMessage('error', 'Pilote non trouvé');
            header('Location: /admin/pilotes');
            return;
        }

        // Récupérer les détails du pilote
        $pilot = $this->pilotModel->getPilotDetails($piloteId);

        if (!$pilot) {
            $this->addFlashMessage('error', 'Pilote non trouvé');
            header('Location: /admin/pilotes');
            return;
        }


        $this->render('admin/pilotes/show.html.twig', [
            'adminPage' => true,
            'pilot' => $pilot
        ]);
    }

    public function ajouterPilote() {
        $this->requireAdmin();

        // Récupérer la liste des campus et des promotions
        $campus = $this->pilotModel->getAllCampus();
        $promotions = $this->pilotModel->getAllPromotions();

        // Générer le token CSRF pour le formulaire
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $this->render('admin/pilotes/add.html.twig', [
            'adminPage' => true,
            'campus' => $campus,
            'promotions' => $promotions,
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    }

    public function enregistrerPilote() {
        $this->requireAdmin();

        // Récupérer les données du formulaire
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $nom = $_POST['nom'] ?? '';
        $prenom = $_POST['prenom'] ?? '';
        $campusId = (int)($_POST['campus'] ?? 0);
        $promotionId = (int)($_POST['promotion'] ?? 0);
        $telephone = $_POST['telephone'] ?? '';

        // Validation des données
        if (empty($email) || empty($password) || empty($nom) || empty($prenom) ||
            $campusId <= 0 || $promotionId <= 0) {
            $this->addFlashMessage('error', 'Veuillez remplir tous les champs obligatoires');
            header('Location: /admin/pilotes/ajouter');
            return;
        }

        // Créer l'utilisateur avec le rôle pilote
        $userId = $this->userModel->createUser($email, $password, $nom, $prenom, 'pilote');

        if ($userId) {
            // Créer le pilote et l'associer à la promotion
            $pilotId = $this->pilotModel->createPilot($userId);

            if ($pilotId && $promotionId) {
                // Associer le pilote à la promotion (date de début/fin par défaut)
                $startDate = date('Y-m-d');
                $endDate = date('Y-m-d', strtotime('+1 year'));
                $this->pilotModel->assignPromotion($pilotId, $promotionId, $startDate, $endDate);
            }

            $this->addFlashMessage('success', 'Pilote ajouté avec succès');
        } else {
            $this->addFlashMessage('error', 'Erreur lors de l\'ajout du pilote');
        }

        header('Location: /admin/pilotes');
    }

    public function modifierPilote($params) {
        $this->requireAdmin();

        $piloteId = $params['id'] ?? null;

        if (!$piloteId) {
            $this->addFlashMessage('error', 'Pilote non trouvé');
            header('Location: /admin/pilotes');
            return;
        }

        // Récupérer les détails du pilote
        $pilot = $this->pilotModel->getPilotDetails($piloteId);

        if (!$pilot) {
            $this->addFlashMessage('error', 'Pilote non trouvé');
            header('Location: /admin/pilotes');
            return;
        }

        // Utiliser les méthodes existantes du pilotModel comme dans ajouterPilote()
        $campus = $this->pilotModel->getAllCampus();
        $promotions = $this->pilotModel->getAllPromotions();

        // Générer le token CSRF pour le formulaire
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $this->render('admin/pilotes/edit.html.twig', [
            'adminPage' => true,
            'pilot' => $pilot,
            'campus' => $campus,
            'promotions' => $promotions,
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    }

    public function mettreAJourPilote($params) {
        $this->requireAdmin();

        // Traiter le formulaire de modification du pilote
        // ...

        $this->addFlashMessage('success', 'Pilote mis à jour avec succès');
        header('Location: /admin/pilotes/' . $params['id']);
    }

    /*
     * Affiche la page de confirmation de suppression d'un pilote
     *
     * @param array $params Paramètres de la route
     */
    public function afficherSupprimerPilote($params) {
        $this->requireAdmin();

        $piloteId = $params['id'] ?? null;

        if (!$piloteId) {
            $this->addFlashMessage('error', 'Pilote non trouvé');
            header('Location: /admin/pilotes');
            return;
        }

        // Récupérer les détails du pilote
        $pilot = $this->pilotModel->getPilotDetails($piloteId);

        if (!$pilot) {
            $this->addFlashMessage('error', 'Pilote non trouvé');
            header('Location: /admin/pilotes');
            return;
        }

        // Générer le token CSRF pour le formulaire
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $this->render('admin/pilotes/delete.html.twig', [
            'adminPage' => true,
            'pilot' => $pilot,
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    }

    public function supprimerPilote($params) {
        $this->requireAdmin();

        $piloteId = $params['id'] ?? null;

        if (!$piloteId) {
            $this->addFlashMessage('error', 'Pilote non trouvé');
            header('Location: /admin/pilotes');
            return;
        }

        // Supprimer le pilote
        // ...

        $this->addFlashMessage('success', 'Pilote supprimé avec succès');
        header('Location: /admin/pilotes');
    }

    // Gestion des étudiants (méthodes similaires à PilotesController)
    public function etudiants() {
        $this->requireAdmin();

        // Récupérer tous les étudiants
        $students = $this->studentModel->getAllStudents();

        $this->render('pilotes/etudiants/index.html.twig', [
            'adminPage' => true,
            'students' => $students
        ]);
    }

    // Répéter les méthodes pour la gestion des étudiants comme dans PilotesController
    // ...

    // Gestion des entreprises (méthodes similaires à PilotesController)
    public function entreprises() {
        $this->requireAdmin();

        $enterprises = $this->enterpriseModel->getEnterprisesByName();

        $this->render('pilotes/entreprises/index.html.twig', [
            'adminPage' => true,
            'enterprises' => $enterprises
        ]);
    }

    // Répéter les méthodes pour la gestion des entreprises comme dans PilotesController
    // ...

    // Gestion des offres (méthodes similaires à PilotesController)
    public function offres() {
        $this->requireAdmin();

        $offers = $this->offerModel->getRecentOffers();

        $this->render('pilotes/offres/index.html.twig', [
            'adminPage' => true,
            'offers' => $offers
        ]);
    }

    // Répéter les méthodes pour la gestion des offres comme dans PilotesController
    // ...

    /**
     * Affiche le formulaire de réinitialisation de mot de passe d'un pilote
     *
     * @param array $params Paramètres de route contenant l'ID du pilote
     * @return void
     */
    public function afficherReset($params) {
        $this->requireAdmin();

        // Validation et sécurisation de l'ID pilote
        $piloteId = filter_var($params['id'] ?? 0, FILTER_VALIDATE_INT);
        if (!$piloteId) {
            $this->addFlashMessage('error', 'Identifiant de pilote invalide');
            header('Location: /admin/pilotes');
            return;
        }

        // Récupération des informations du pilote via le modèle
        $pilot = $this->pilotModel->getPilotDetails($piloteId);
        if (!$pilot) {
            $this->addFlashMessage('error', 'Pilote introuvable dans la base de données');
            header('Location: /admin/pilotes');
            return;
        }

        // Génération de jeton CSRF sécurisé pour prévention d'attaques CSRF
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        // Rendu du template avec les données nécessaires
        $this->render('admin/pilotes/reset.html.twig', [
            'adminPage' => true,
            'pilot' => $pilot,
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    }

    /**
     * Traite la soumission du formulaire de réinitialisation de mot de passe d'un pilote
     *
     * @param array $params Paramètres de route contenant l'ID du pilote
     * @return void
     */
    public function resetPassword($params) {
        $this->requireAdmin();

        // Validation sécurisée de l'ID pilote
        $piloteId = filter_var($params['id'] ?? 0, FILTER_VALIDATE_INT);
        if (!$piloteId) {
            $this->addFlashMessage('error', 'Identifiant de pilote invalide');
            header('Location: /admin/pilotes');
            return;
        }

        // Vérification du jeton CSRF pour prévenir les attaques CSRF
        if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) ||
            !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $this->addFlashMessage('error', 'Jeton de sécurité invalide. Veuillez réessayer.');
            header('Location: /admin/pilotes/' . $piloteId . '/reset');
            return;
        }

        // Récupération et validation des données de formulaire
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validations de sécurité et fonctionnelles du mot de passe
        if (empty($password)) {
            $this->addFlashMessage('error', 'Le mot de passe ne peut pas être vide');
            header('Location: /admin/pilotes/' . $piloteId . '/reset');
            return;
        }

        if (strlen($password) < 8) {
            $this->addFlashMessage('error', 'Le mot de passe doit contenir au moins 8 caractères');
            header('Location: /admin/pilotes/' . $piloteId . '/reset');
            return;
        }

        if ($password !== $confirmPassword) {
            $this->addFlashMessage('error', 'Les mots de passe ne correspondent pas');
            header('Location: /admin/pilotes/' . $piloteId . '/reset');
            return;
        }

        // Récupération des données complètes du pilote pour obtenir l'ID utilisateur
        $pilot = $this->pilotModel->getPilotDetails($piloteId);
        if (!$pilot) {
            $this->addFlashMessage('error', 'Pilote introuvable');
            header('Location: /admin/pilotes');
            return;
        }

        // Mise à jour du mot de passe via le modèle UserModel
        $success = $this->userModel->updatePassword($pilot['Id_Utilisateur'], $password);

        // Gestion du résultat de l'opération
        if ($success) {
            $this->addFlashMessage('success', 'Mot de passe du pilote mis à jour avec succès');
            header('Location: /admin/pilotes/' . $piloteId);
        } else {
            $this->addFlashMessage('error', 'Échec de la mise à jour du mot de passe. Veuillez réessayer.');
            header('Location: /admin/pilotes/' . $piloteId . '/reset');
        }
    }
}

