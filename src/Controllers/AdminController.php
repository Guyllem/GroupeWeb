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

        // Récupérer tous les pilotes sans restriction
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
            'pilot' => $pilot,
            'csrf_token' => $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32))
        ]);
    }

    public function ajouterPilote() {
        $this->requireAdmin();

        // Récupérer la liste des campus et des promotions
        $campus = $this->pilotModel->getAllCampus();
        $promotions = $this->pilotModel->getAllPromotions();

        // Générer le token CSRF pour le formulaire
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

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
                // Associer le pilote à la promotion
                $startDate = date('Y-m-d');
                $endDate = date('Y-m-d', strtotime('+1 year'));
                $this->pilotModel->assignPromotion($pilotId, $promotionId, $startDate, $endDate);
            }

            $this->addFlashMessage('success', 'Pilote ajouté avec succès');
            header('Location: /admin/pilotes');
        } else {
            $this->addFlashMessage('error', 'Erreur lors de l\'ajout du pilote');
            header('Location: /admin/pilotes/ajouter');
        }
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

        // Récupérer la liste des campus et des promotions
        $campus = $this->pilotModel->getAllCampus();
        $promotions = $this->pilotModel->getAllPromotions();

        // Générer le token CSRF pour le formulaire
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

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

        $piloteId = $params['id'] ?? null;

        if (!$piloteId) {
            $this->addFlashMessage('error', 'Pilote non trouvé');
            header('Location: /admin/pilotes');
            return;
        }

        // Récupérer les données du formulaire
        $email = $_POST['email'] ?? '';
        $nom = $_POST['nom'] ?? '';
        $prenom = $_POST['prenom'] ?? '';
        $promotionId = (int)($_POST['promotion'] ?? 0);
        $telephone = $_POST['telephone'] ?? '';

        // Validation des données
        if (empty($email) || empty($nom) || empty($prenom) || $promotionId <= 0) {
            $this->addFlashMessage('error', 'Veuillez remplir tous les champs obligatoires');
            header('Location: /admin/pilotes/' . $piloteId . '/modifier');
            return;
        }

        // Récupérer l'ID utilisateur du pilote
        $pilot = $this->pilotModel->getPilotDetails($piloteId);

        if (!$pilot) {
            $this->addFlashMessage('error', 'Pilote non trouvé');
            header('Location: /admin/pilotes');
            return;
        }

        // Mettre à jour les informations utilisateur
        $updateUser = $this->userModel->updateUser(
            $pilot['Id_Utilisateur'],
            $email,
            null, // pas de changement de mot de passe ici
            $nom,
            $prenom,
            $telephone
        );

        // Mettre à jour la promotion du pilote si nécessaire
        if ($promotionId && isset($pilot['promotions'][0]) && $promotionId != $pilot['promotions'][0]['Id_Promotion']) {
            $startDate = date('Y-m-d');
            $endDate = date('Y-m-d', strtotime('+1 year'));
            $this->pilotModel->assignPromotion($piloteId, $promotionId, $startDate, $endDate);
        }

        $this->addFlashMessage('success', 'Pilote mis à jour avec succès');
        header('Location: /admin/pilotes/' . $piloteId);
    }

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
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

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

        // Récupérer l'ID utilisateur du pilote
        $pilot = $this->pilotModel->getPilotDetails($piloteId);

        if (!$pilot) {
            $this->addFlashMessage('error', 'Pilote non trouvé');
            header('Location: /admin/pilotes');
            return;
        }

        // Supprimer le pilote et l'utilisateur associé
        $success = $this->pilotModel->delete($piloteId);

        if ($success) {
            // Supprimer l'utilisateur associé
            $this->userModel->delete($pilot['Id_Utilisateur']);
            $this->addFlashMessage('success', 'Pilote supprimé avec succès');
        } else {
            $this->addFlashMessage('error', 'Erreur lors de la suppression du pilote');
        }

        header('Location: /admin/pilotes');
    }

    // Gestion des étudiants
    public function etudiants() {
        $this->requireAdmin();

        // Récupérer tous les étudiants sans restriction par pilote
        // Contrairement à PilotesController qui filtre par $pilotId
        $students = $this->studentModel->getAllStudents();

        $this->render('admin/etudiants/index.html.twig', [
            'adminPage' => true,
            'students' => $students
        ]);
    }

    public function etudiantDetails($params) {
        $this->requireAdmin();

        $etudiantId = $params['id'] ?? null;

        if (!$etudiantId) {
            $this->addFlashMessage('error', 'Étudiant non trouvé');
            header('Location: /admin/etudiants');
            return;
        }

        // Récupérer les détails de l'étudiant
        $student = $this->studentModel->getStudentInfo($etudiantId);

        if (!$student) {
            $this->addFlashMessage('error', 'Étudiant non trouvé');
            header('Location: /admin/etudiants');
            return;
        }

        // Récupérer les compétences de l'étudiant
        $student['skills'] = $this->studentModel->getStudentSkills($etudiantId);

        // Générer le token CSRF pour le formulaire
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        $this->render('admin/etudiants/show.html.twig', [
            'adminPage' => true,
            'student' => $student,
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    }

    public function ajouterEtudiant() {
        $this->requireAdmin();

        // Récupérer la liste des promotions et des campus
        $promotions = $this->pilotModel->getAllPromotions();
        $campus = $this->pilotModel->getAllCampus();

        // Générer le token CSRF pour le formulaire
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        $this->render('admin/etudiants/add.html.twig', [
            'adminPage' => true,
            'promotions' => $promotions,
            'campus' => $campus,
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    }

    public function enregistrerEtudiant() {
        $this->requireAdmin();

        // Récupérer les données du formulaire
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $nom = $_POST['nom'] ?? '';
        $prenom = $_POST['prenom'] ?? '';
        $promotionId = (int)($_POST['promotion'] ?? 0);
        $campusId = (int)($_POST['campus'] ?? 0);
        $telephone = $_POST['telephone'] ?? '';

        // Validation des données
        if (empty($email) || empty($password) || empty($nom) || empty($prenom) ||
            $promotionId <= 0 || $campusId <= 0) {
            $this->addFlashMessage('error', 'Veuillez remplir tous les champs obligatoires');
            header('Location: /admin/etudiants/ajouter');
            return;
        }

        // Créer l'utilisateur avec le rôle étudiant
        $userId = $this->userModel->createUser($email, $password, $nom, $prenom, 'etudiant');

        if ($userId) {
            // Récupérer l'ID étudiant généré
            $studentId = $this->studentModel->getStudentIdFromUserId($userId);

            if ($studentId && $promotionId) {
                // Associer l'étudiant à la promotion
                $startDate = date('Y-m-d');
                $endDate = date('Y-m-d', strtotime('+1 year'));

                // Utilisation d'une méthode que vous devrez implémenter dans StudentModel
                $this->studentModel->assignToPromotion($studentId, $promotionId, $startDate, $endDate);
            }

            $this->addFlashMessage('success', 'Étudiant ajouté avec succès');
            header('Location: /admin/etudiants');
        } else {
            $this->addFlashMessage('error', 'Erreur lors de l\'ajout de l\'étudiant');
            header('Location: /admin/etudiants/ajouter');
        }
    }

    public function modifierEtudiant($params) {
        $this->requireAdmin();

        $etudiantId = $params['id'] ?? null;

        if (!$etudiantId) {
            $this->addFlashMessage('error', 'Étudiant non trouvé');
            header('Location: /admin/etudiants');
            return;
        }

        // Récupérer les détails de l'étudiant
        $student = $this->studentModel->getStudentInfo($etudiantId);

        if (!$student) {
            $this->addFlashMessage('error', 'Étudiant non trouvé');
            header('Location: /admin/etudiants');
            return;
        }

        // Récupérer les compétences de l'étudiant
        $student['skills'] = $this->studentModel->getStudentSkills($etudiantId);

        // Récupérer la liste des promotions et des campus
        $promotions = $this->pilotModel->getAllPromotions();
        $campus = $this->pilotModel->getAllCampus();

        // Générer le token CSRF pour le formulaire
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        $this->render('admin/etudiants/edit.html.twig', [
            'adminPage' => true,
            'student' => $student,
            'promotions' => $promotions,
            'campus' => $campus,
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    }

    public function mettreAJourEtudiant($params) {
        $this->requireAdmin();

        $etudiantId = $params['id'] ?? null;

        if (!$etudiantId) {
            $this->addFlashMessage('error', 'Étudiant non trouvé');
            header('Location: /admin/etudiants');
            return;
        }

        // Récupérer les données du formulaire
        $email = $_POST['email'] ?? '';
        $nom = $_POST['nom'] ?? '';
        $prenom = $_POST['prenom'] ?? '';
        $promotionId = (int)($_POST['promotion'] ?? 0);
        $telephone = $_POST['telephone'] ?? '';

        // Validation des données
        if (empty($email) || empty($nom) || empty($prenom) || $promotionId <= 0) {
            $this->addFlashMessage('error', 'Veuillez remplir tous les champs obligatoires');
            header('Location: /admin/etudiants/' . $etudiantId . '/modifier');
            return;
        }

        // Récupérer l'ID utilisateur de l'étudiant
        $student = $this->studentModel->getStudentInfo($etudiantId);

        if (!$student) {
            $this->addFlashMessage('error', 'Étudiant non trouvé');
            header('Location: /admin/etudiants');
            return;
        }

        // Mettre à jour les informations utilisateur
        $updateUser = $this->userModel->updateUser(
            $student['Id_Utilisateur'],
            $email,
            null, // pas de changement de mot de passe ici
            $nom,
            $prenom,
            $telephone
        );

        // Mettre à jour la promotion de l'étudiant si nécessaire
        if ($promotionId && $promotionId != $student['Id_Promotion']) {
            $startDate = date('Y-m-d');
            $endDate = date('Y-m-d', strtotime('+1 year'));

            // Utiliser la méthode assignToPromotion à implémenter
            $this->studentModel->assignToPromotion($etudiantId, $promotionId, $startDate, $endDate);
        }

        $this->addFlashMessage('success', 'Étudiant mis à jour avec succès');
        header('Location: /admin/etudiants/' . $etudiantId);
    }

    public function supprimerEtudiant($params) {
        $this->requireAdmin();

        $etudiantId = $params['id'] ?? null;

        if (!$etudiantId) {
            $this->addFlashMessage('error', 'Étudiant non trouvé');
            header('Location: /admin/etudiants');
            return;
        }

        // Récupérer l'ID utilisateur de l'étudiant
        $student = $this->studentModel->getStudentInfo($etudiantId);

        if (!$student) {
            $this->addFlashMessage('error', 'Étudiant non trouvé');
            header('Location: /admin/etudiants');
            return;
        }

        // Supprimer l'étudiant et l'utilisateur associé
        $success = $this->studentModel->delete($etudiantId);

        if ($success) {
            // Supprimer l'utilisateur associé
            $this->userModel->delete($student['Id_Utilisateur']);
            $this->addFlashMessage('success', 'Étudiant supprimé avec succès');
        } else {
            $this->addFlashMessage('error', 'Erreur lors de la suppression de l\'étudiant');
        }

        header('Location: /admin/etudiants');
    }

    /* ========= GESTION DES ENTREPRISES ========= */

    /**
     * Affiche la liste de toutes les entreprises
     */
    public function entreprises() {
        $this->requireAdmin();

        // Récupérer toutes les entreprises sans restriction
        $enterprises = $this->enterpriseModel->getEnterprisesByName(50); // Limite augmentée pour admin

        $this->render('admin/entreprises/index.html.twig', [
            'adminPage' => true,
            'enterprises' => $enterprises
        ]);
    }

    /**
     * Affiche les détails d'une entreprise spécifique
     *
     * @param array $params Paramètres de la route
     */
    public function entrepriseDetails($params) {
        $this->requireAdmin();

        $enterpriseId = $params['id'] ?? null;

        if (!$enterpriseId) {
            $this->addFlashMessage('error', 'Entreprise non trouvée');
            header('Location: /admin/entreprises');
            return;
        }

        // Récupérer les détails complets de l'entreprise
        $enterprise = $this->enterpriseModel->getEnterpriseDetails($enterpriseId);

        if (!$enterprise) {
            $this->addFlashMessage('error', 'Entreprise non trouvée');
            header('Location: /admin/entreprises');
            return;
        }

        // Récupérer également les offres associées pour contexte complet
        $offers = $this->offerModel->getOffersByEnterprise($enterpriseId);

        $this->render('admin/entreprises/show.html.twig', [
            'adminPage' => true,
            'enterprise' => $enterprise,
            'offers' => $offers,
            'csrf_token' => $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32))
        ]);
    }

    /**
     * Affiche le formulaire d'ajout d'une entreprise
     */
    public function ajouterEntreprise() {
        $this->requireAdmin();

        // Générer le token CSRF pour la sécurité du formulaire
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        $this->render('admin/entreprises/add.html.twig', [
            'adminPage' => true,
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    }

    /**
     * Traite la soumission du formulaire d'ajout d'entreprise
     */
    public function enregistrerEntreprise() {
        $this->requireAdmin();

        // Validation du token CSRF
        if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) ||
            $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $this->addFlashMessage('error', 'Erreur de sécurité lors de la soumission du formulaire');
            header('Location: /admin/entreprises/ajouter');
            return;
        }

        // Récupération et validation des données essentielles
        $nom = trim($_POST['nom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $ville = trim($_POST['ville'] ?? '');
        $codePostal = trim($_POST['code_postal'] ?? '');
        $adresse = trim($_POST['adresse'] ?? '');
        $secteurs = $_POST['secteurs'] ?? '';
        $telephone = trim($_POST['telephone'] ?? '');
        $effectif = (int)($_POST['effectif'] ?? 0);

        if (empty($nom) || empty($email) || empty($ville) || empty($codePostal)) {
            $this->addFlashMessage('error', 'Veuillez remplir tous les champs obligatoires');
            header('Location: /admin/entreprises/ajouter');
            return;
        }

        // Préparation des données structurées pour le modèle
        $enterpriseData = [
            'nom' => $nom,
            'description' => $description,
            'email' => $email,
            'telephone' => $telephone,
            'effectif' => $effectif,
            'ville' => $ville,
            'codePostal' => $codePostal,
            'adresse' => $adresse
        ];

        // Traitement des secteurs comme tableau si envoyé sous forme de chaîne
        $secteursArray = [];
        if (is_string($secteurs) && !empty($secteurs)) {
            $secteursArray = array_map('trim', explode(',', $secteurs));
        } elseif (is_array($secteurs)) {
            $secteursArray = $secteurs;
        }

        // Création de l'entreprise avec secteurs associés
        $enterpriseId = $this->enterpriseModel->createEnterprise($enterpriseData, $secteursArray);

        if ($enterpriseId) {
            $this->addFlashMessage('success', 'Entreprise ajoutée avec succès');
            header('Location: /admin/entreprises/' . $enterpriseId);
        } else {
            $this->addFlashMessage('error', 'Erreur lors de l\'ajout de l\'entreprise');
            header('Location: /admin/entreprises/ajouter');
        }
    }

    /**
     * Affiche le formulaire de modification d'une entreprise
     *
     * @param array $params Paramètres de la route
     */
    public function modifierEntreprise($params) {
        $this->requireAdmin();

        $enterpriseId = $params['id'] ?? null;

        if (!$enterpriseId) {
            $this->addFlashMessage('error', 'Entreprise non trouvée');
            header('Location: /admin/entreprises');
            return;
        }

        // Charger les données existantes de l'entreprise
        $enterprise = $this->enterpriseModel->getEnterpriseDetails($enterpriseId);

        if (!$enterprise) {
            $this->addFlashMessage('error', 'Entreprise non trouvée');
            header('Location: /admin/entreprises');
            return;
        }

        // Générer le token CSRF pour la sécurité du formulaire
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        $this->render('admin/entreprises/edit.html.twig', [
            'adminPage' => true,
            'enterprise' => $enterprise,
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    }

    /**
     * Traite la soumission du formulaire de modification d'entreprise
     *
     * @param array $params Paramètres de la route
     */
    public function mettreAJourEntreprise($params) {
        $this->requireAdmin();

        $enterpriseId = $params['id'] ?? null;

        if (!$enterpriseId) {
            $this->addFlashMessage('error', 'Entreprise non trouvée');
            header('Location: /admin/entreprises');
            return;
        }

        // Validation du token CSRF
        if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) ||
            $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $this->addFlashMessage('error', 'Erreur de sécurité lors de la soumission du formulaire');
            header('Location: /admin/entreprises/' . $enterpriseId . '/modifier');
            return;
        }

        // Récupération et validation des données
        $nom = trim($_POST['nom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $ville = trim($_POST['ville'] ?? '');
        $codePostal = trim($_POST['code_postal'] ?? '');
        $adresse = trim($_POST['adresse'] ?? '');
        $secteurs = $_POST['secteurs'] ?? '';
        $telephone = trim($_POST['telephone'] ?? '');
        $effectif = (int)($_POST['effectif'] ?? 0);

        if (empty($nom) || empty($email) || empty($ville) || empty($codePostal)) {
            $this->addFlashMessage('error', 'Veuillez remplir tous les champs obligatoires');
            header('Location: /admin/entreprises/' . $enterpriseId . '/modifier');
            return;
        }

        // Préparation des données structurées
        $enterpriseData = [
            'nom' => $nom,
            'description' => $description,
            'email' => $email,
            'telephone' => $telephone,
            'effectif' => $effectif,
            'ville' => $ville,
            'codePostal' => $codePostal,
            'adresse' => $adresse
        ];

        // Traitement des secteurs (similaire à enregistrerEntreprise)
        $secteursArray = [];
        if (is_string($secteurs) && !empty($secteurs)) {
            $secteursArray = array_map('trim', explode(',', $secteurs));
        } elseif (is_array($secteurs)) {
            $secteursArray = $secteurs;
        }

        // Mise à jour de l'entreprise
        $success = $this->enterpriseModel->updateEnterprise($enterpriseId, $enterpriseData, $secteursArray);

        if ($success) {
            $this->addFlashMessage('success', 'Entreprise mise à jour avec succès');
            header('Location: /admin/entreprises/' . $enterpriseId);
        } else {
            $this->addFlashMessage('error', 'Erreur lors de la mise à jour de l\'entreprise');
            header('Location: /admin/entreprises/' . $enterpriseId . '/modifier');
        }
    }

    /**
     * Affiche la page de confirmation de suppression d'une entreprise
     *
     * @param array $params Paramètres de la route
     */
    public function supprimerEntreprise($params) {
        $this->requireAdmin();

        $enterpriseId = $params['id'] ?? null;

        if (!$enterpriseId) {
            $this->addFlashMessage('error', 'Entreprise non trouvée');
            header('Location: /admin/entreprises');
            return;
        }

        // Vérifier si l'entreprise existe
        $enterprise = $this->enterpriseModel->getEnterpriseDetails($enterpriseId);

        if (!$enterprise) {
            $this->addFlashMessage('error', 'Entreprise non trouvée');
            header('Location: /admin/entreprises');
            return;
        }

        // Générer le token CSRF
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        // Page de confirmation
        $this->render('admin/entreprises/delete.html.twig', [
            'adminPage' => true,
            'enterprise' => $enterprise,
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    }

    /**
     * Traite la suppression d'une entreprise après confirmation
     *
     * @param array $params Paramètres de la route
     */
    public function confirmerSuppressionEntreprise($params) {
        $this->requireAdmin();

        $enterpriseId = $params['id'] ?? null;

        // Validation du token CSRF
        if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) ||
            $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $this->addFlashMessage('error', 'Erreur de sécurité');
            header('Location: /admin/entreprises');
            return;
        }

        if (!$enterpriseId) {
            $this->addFlashMessage('error', 'Entreprise non trouvée');
            header('Location: /admin/entreprises');
            return;
        }

        // Opération de suppression avec protection transactionnelle
        $success = $this->enterpriseModel->delete($enterpriseId);

        if ($success) {
            $this->addFlashMessage('success', 'Entreprise supprimée avec succès');
        } else {
            $this->addFlashMessage('error', 'Erreur lors de la suppression de l\'entreprise');
        }

        header('Location: /admin/entreprises');
    }

    /* ========= GESTION DES OFFRES ========= */

    /**
     * Affiche la liste des offres
     */
    public function offres() {
        $this->requireAdmin();

        // Récupérer toutes les offres sans restriction
        // Augmentation de la limite pour l'admin pour voir plus d'offres
        $offers = $this->offerModel->getRecentOffers(50);

        $this->render('admin/offres/index.html.twig', [
            'adminPage' => true,
            'offers' => $offers
        ]);
    }

    /**
     * Affiche les détails d'une offre
     *
     * @param array $params Paramètres de la route
     */
    public function offreDetails($params) {
        $this->requireAdmin();

        $offerId = $params['id'] ?? null;

        if (!$offerId) {
            $this->addFlashMessage('error', 'Offre non trouvée');
            header('Location: /admin/offres');
            return;
        }

        // Récupérer les détails complets de l'offre
        $offer = $this->offerModel->getOfferDetails($offerId);

        if (!$offer) {
            $this->addFlashMessage('error', 'Offre non trouvée');
            header('Location: /admin/offres');
            return;
        }

        // Récupérer les candidatures pour cette offre (spécifique à l'admin)
        $applications = $this->offerModel->getOfferApplications($offerId);

        $this->render('admin/offres/show.html.twig', [
            'adminPage' => true,
            'offer' => $offer,
            'applications' => $applications,
            'csrf_token' => $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32))
        ]);
    }

    /**
     * Affiche le formulaire d'ajout d'une offre
     */
    public function ajouterOffre() {
        $this->requireAdmin();

        // Récupérer les données nécessaires pour le formulaire
        $enterprises = $this->enterpriseModel->getAll('Nom_Entreprise');
        $competences = $this->offerModel->getAllCompetences();

        // Générer le token CSRF
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        $this->render('admin/offres/add.html.twig', [
            'adminPage' => true,
            'enterprises' => $enterprises,
            'competences' => $competences,
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    }

    /**
     * Traite la soumission du formulaire d'ajout d'offre
     */
    public function enregistrerOffre() {
        $this->requireAdmin();

        // Validation du token CSRF
        if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) ||
            $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $this->addFlashMessage('error', 'Erreur de sécurité lors de la soumission du formulaire');
            header('Location: /admin/offres/ajouter');
            return;
        }

        // Récupération et validation des données
        $titre = trim($_POST['titre'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $remuneration = (float)($_POST['remuneration'] ?? 0);
        $niveauRequis = $_POST['niveau_requis'] ?? '';
        $dateDebut = $_POST['date_debut'] ?? '';
        $dureeMin = (int)($_POST['duree_min'] ?? 0);
        $dureeMax = (int)($_POST['duree_max'] ?? 0);
        $idEntreprise = (int)($_POST['entreprise'] ?? 0);

        // Récupération des compétences (peut être un array ou une chaîne)
        $competences = [];
        if (isset($_POST['competences'])) {
            $competences = is_array($_POST['competences']) ? $_POST['competences'] : [$_POST['competences']];
        }

        // Validation basique
        if (empty($titre) || empty($description) || empty($niveauRequis) ||
            empty($dateDebut) || $dureeMin <= 0 || $idEntreprise <= 0) {
            $this->addFlashMessage('error', 'Veuillez remplir tous les champs obligatoires correctement');
            header('Location: /admin/offres/ajouter');
            return;
        }

        // Structure des données pour le modèle
        $offerData = [
            'titre' => $titre,
            'description' => $description,
            'remuneration' => $remuneration,
            'niveauRequis' => $niveauRequis,
            'dateDebut' => $dateDebut,
            'dureeMin' => $dureeMin,
            'dureeMax' => max($dureeMin, $dureeMax), // Protection contre durée max < min
            'idEntreprise' => $idEntreprise
        ];

        // Création de l'offre avec ses compétences associées
        $offerId = $this->offerModel->createOffer($offerData, $competences);

        if ($offerId) {
            $this->addFlashMessage('success', 'Offre ajoutée avec succès');
            header('Location: /admin/offres/' . $offerId);
        } else {
            $this->addFlashMessage('error', 'Erreur lors de l\'ajout de l\'offre');
            header('Location: /admin/offres/ajouter');
        }
    }

    /**
     * Affiche le formulaire de modification d'une offre
     *
     * @param array $params Paramètres de la route
     */
    public function modifierOffre($params) {
        $this->requireAdmin();

        $offerId = $params['id'] ?? null;

        if (!$offerId) {
            $this->addFlashMessage('error', 'Offre non trouvée');
            header('Location: /admin/offres');
            return;
        }

        // Récupérer les données de l'offre
        $offer = $this->offerModel->getOfferDetails($offerId);

        if (!$offer) {
            $this->addFlashMessage('error', 'Offre non trouvée');
            header('Location: /admin/offres');
            return;
        }

        // Récupérer les données pour les listes déroulantes
        $enterprises = $this->enterpriseModel->getAll('Nom_Entreprise');
        $competences = $this->offerModel->getAllCompetences();

        // Traitement des compétences déjà sélectionnées
        $selectedCompetences = array_map(function($skill) {
            return $skill['Id_Competence'];
        }, $offer['skills'] ?? []);

        // Générer le token CSRF
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        $this->render('admin/offres/edit.html.twig', [
            'adminPage' => true,
            'offer' => $offer,
            'enterprises' => $enterprises,
            'competences' => $competences,
            'selected_competences' => $selectedCompetences,
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    }

    /**
     * Traite la soumission du formulaire de modification d'offre
     *
     * @param array $params Paramètres de la route
     */
    public function mettreAJourOffre($params) {
        $this->requireAdmin();

        $offerId = $params['id'] ?? null;

        if (!$offerId) {
            $this->addFlashMessage('error', 'Offre non trouvée');
            header('Location: /admin/offres');
            return;
        }

        // Validation du token CSRF
        if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) ||
            $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $this->addFlashMessage('error', 'Erreur de sécurité');
            header('Location: /admin/offres/' . $offerId . '/modifier');
            return;
        }

        // Récupération et validation similaire à enregistrerOffre
        $titre = trim($_POST['titre'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $remuneration = (float)($_POST['remuneration'] ?? 0);
        $niveauRequis = $_POST['niveau_requis'] ?? '';
        $dateDebut = $_POST['date_debut'] ?? '';
        $dureeMin = (int)($_POST['duree_min'] ?? 0);
        $dureeMax = (int)($_POST['duree_max'] ?? 0);
        $idEntreprise = (int)($_POST['entreprise'] ?? 0);

        // Récupération des compétences
        $competences = [];
        if (isset($_POST['competences'])) {
            $competences = is_array($_POST['competences']) ? $_POST['competences'] : [$_POST['competences']];
        }

        // Validation
        if (empty($titre) || empty($description) || empty($niveauRequis) ||
            empty($dateDebut) || $dureeMin <= 0 || $idEntreprise <= 0) {
            $this->addFlashMessage('error', 'Veuillez remplir tous les champs obligatoires correctement');
            header('Location: /admin/offres/' . $offerId . '/modifier');
            return;
        }

        // Structuration des données
        $offerData = [
            'titre' => $titre,
            'description' => $description,
            'remuneration' => $remuneration,
            'niveauRequis' => $niveauRequis,
            'dateDebut' => $dateDebut,
            'dureeMin' => $dureeMin,
            'dureeMax' => max($dureeMin, $dureeMax),
            'idEntreprise' => $idEntreprise
        ];

        // Mise à jour de l'offre
        $success = $this->offerModel->updateOffer($offerId, $offerData, $competences);

        if ($success) {
            $this->addFlashMessage('success', 'Offre mise à jour avec succès');
            header('Location: /admin/offres/' . $offerId);
        } else {
            $this->addFlashMessage('error', 'Erreur lors de la mise à jour de l\'offre');
            header('Location: /admin/offres/' . $offerId . '/modifier');
        }
    }

    /**
     * Affiche la page de confirmation de suppression d'une offre
     *
     * @param array $params Paramètres de la route
     */
    public function supprimerOffre($params) {
        $this->requireAdmin();

        $offerId = $params['id'] ?? null;

        if (!$offerId) {
            $this->addFlashMessage('error', 'Offre non trouvée');
            header('Location: /admin/offres');
            return;
        }

        // Récupérer les détails de l'offre
        $offer = $this->offerModel->getOfferDetails($offerId);

        if (!$offer) {
            $this->addFlashMessage('error', 'Offre non trouvée');
            header('Location: /admin/offres');
            return;
        }

        // Génération du token CSRF
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        $this->render('admin/offres/delete.html.twig', [
            'adminPage' => true,
            'offer' => $offer,
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    }

    /**
     * Traite la suppression d'une offre après confirmation
     *
     * @param array $params Paramètres de la route
     */
    public function confirmerSuppressionOffre($params) {
        $this->requireAdmin();

        $offerId = $params['id'] ?? null;

        // Validation du token CSRF
        if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) ||
            $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $this->addFlashMessage('error', 'Erreur de sécurité');
            header('Location: /admin/offres');
            return;
        }

        if (!$offerId) {
            $this->addFlashMessage('error', 'Offre non trouvée');
            header('Location: /admin/offres');
            return;
        }

        // Opération de suppression
        $success = $this->offerModel->delete($offerId);

        if ($success) {
            $this->addFlashMessage('success', 'Offre supprimée avec succès');
        } else {
            $this->addFlashMessage('error', 'Erreur lors de la suppression de l\'offre');
        }

        header('Location: /admin/offres');
    }
}