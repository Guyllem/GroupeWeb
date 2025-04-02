<?php
namespace App\Controllers;

use App\Models\PilotModel;
use App\Models\StudentModel;
use App\Models\EnterpriseModel;
use App\Models\OfferModel;
use App\Models\UserModel;

class PilotesController extends BaseController {
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
        $this->requirePilote();
        echo $this->twig->render('pilotes/index.html.twig');
    }

    // Gestion des étudiants
    public function etudiants() {
        $this->requirePilote();

        // Récupérer le pilote actuel
        $userId = $_SESSION['user_id'];
        $pilotId = $this->pilotModel->getPilotIdFromUserId($userId);

        // Récupérer les étudiants supervisés par ce pilote
        $students = $this->pilotModel->getSupervisedStudents($pilotId);

        echo $this->twig->render('pilotes/etudiants/index.html.twig', [
            'pilotePage' => true,
            'students' => $students
        ]);
    }

    /**
     * Recherche d'étudiants
     */
    public function rechercheEtudiant() {
        $this->requirePilote();

        $userId = $_SESSION['user_id'];
        $pilotId = $this->pilotModel->getPilotIdFromUserId($userId);
        $searchTerm = $_POST['search'] ?? '';

        if (empty($searchTerm)) {
            // Si aucun terme de recherche, rediriger vers la liste complète
            header('Location: /pilotes/etudiants');
            exit;
        }

        // Effectuer la recherche
        $students = $this->pilotModel->searchStudents($searchTerm, $pilotId);

        echo $this->twig->render('pilotes/etudiants/index.html.twig', [
            'pilotePage' => true,
            'students' => $students,
            'searchTerm' => $searchTerm
        ]);
    }

    public function etudiantDetails($params) {
        $this->requirePilote();

        $etudiantId = $params['id'] ?? null;

        if (!$etudiantId) {
            $this->addFlashMessage('error', 'Étudiant non trouvé');
            header('Location: /pilotes/etudiants');
            return;
        }

        // Récupérer les détails de l'étudiant
        $student = $this->studentModel->getStudentInfo($etudiantId);

        if (!$student) {
            $this->addFlashMessage('error', 'Étudiant non trouvé');
            header('Location: /pilotes/etudiants');
            return;
        }

        // Récupérer les compétences de l'étudiant
        $student['skills'] = $this->studentModel->getStudentSkills($etudiantId);

        // Générer le token CSRF pour le formulaire
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        echo $this->twig->render('pilotes/etudiants/show.html.twig', [
            'pilotePage' => true,
            'student' => $student,
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    }

    public function ajouterEtudiant() {
        $this->requirePilote();
        // Afficher le formulaire d'ajout d'étudiant

        // Générer le token CSRF pour le formulaire
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        echo $this->twig->render('pilotes/etudiants/add.html.twig', [
            'pilotePage' => true,
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    }

    public function enregistrerEtudiant() {
        $this->requirePilote();
        // Traiter le formulaire d'ajout d'étudiant
        // Code pour ajouter un étudiant...

        $this->addFlashMessage('success', 'Étudiant ajouté avec succès');
        header('Location: /pilotes/etudiants');
    }

    public function modifierEtudiant($params) {
        $this->requirePilote();

        $etudiantId = $params['id'] ?? null;

        if (!$etudiantId) {
            $this->addFlashMessage('error', 'Étudiant non trouvé');
            header('Location: /pilotes/etudiants');
            return;
        }

        // Récupérer les détails de l'étudiant
        $student = $this->studentModel->getStudentInfo($etudiantId);

        if (!$student) {
            $this->addFlashMessage('error', 'Étudiant non trouvé');
            header('Location: /pilotes/etudiants');
            return;
        }

        // Générer le token CSRF pour le formulaire
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        // Récupérer les compétences de l'étudiant
        $student['skills'] = $this->studentModel->getStudentSkills($etudiantId);

        echo $this->twig->render('pilotes/etudiants/edit.html.twig', [
            'pilotePage' => true,
            'student' => $student,
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    }

    public function mettreAJourEtudiant($params) {
        $this->requirePilote();

        $etudiantId = $params['id'] ?? null;

        if (!$etudiantId) {
            $this->addFlashMessage('error', 'Étudiant non trouvé');
            header('Location: /pilotes/etudiants');
            return;
        }

        // Récupérer les données du formulaire
        $nom = $_POST['nom'] ?? '';
        $prenom = $_POST['prenom'] ?? '';
        $email = $_POST['email'] ?? '';
        $promotionId = $_POST['promotion'] ?? '';
        $telephone = $_POST['telephone'] ?? '';

        // Récupérer l'ID utilisateur de l'étudiant
        $student = $this->studentModel->getStudentInfo($etudiantId);

        if (!$student) {
            $this->addFlashMessage('error', 'Étudiant non trouvé');
            header('Location: /pilotes/etudiants');
            return;
        }

        // Mettre à jour les informations dans la table utilisateur
        $updateUser = $this->userModel->updateUser(
            $student['Id_Utilisateur'],
            $email,
            null, // pas de changement de mot de passe ici
            $nom,
            $prenom
        );

        // Mettre à jour la promotion de l'étudiant si nécessaire
        // Code pour mettre à jour la promotion...

        if ($updateUser) {
            $this->addFlashMessage('success', 'Étudiant mis à jour avec succès');
        } else {
            $this->addFlashMessage('error', 'Erreur lors de la mise à jour de l\'étudiant');
        }

        header('Location: /pilotes/etudiants/' . $etudiantId);
    }

    /**
     * Affiche le formulaire de modification du mot de passe d'un étudiant
     */
    public function etudiantPassword($params) {
        $this->requirePilote();

        $etudiantId = $params['id'] ?? null;

        if (!$etudiantId) {
            $this->addFlashMessage('error', 'Étudiant non trouvé');
            header('Location: /pilotes/etudiants');
            return;
        }

        // Récupérer les détails de l'étudiant
        $student = $this->studentModel->getStudentInfo($etudiantId);

        if (!$student) {
            $this->addFlashMessage('error', 'Étudiant non trouvé');
            header('Location: /pilotes/etudiants');
            return;
        }

        echo $this->twig->render('pilotes/etudiants/password.html.twig', [
            'pilotePage' => true,
            'student' => $student
        ]);
    }

    /**
     * Traite le formulaire de modification du mot de passe d'un étudiant
     */
    public function etudiantSavePassword($params) {
        $this->requirePilote();

        $etudiantId = $params['id'] ?? null;

        if (!$etudiantId) {
            $this->addFlashMessage('error', 'Étudiant non trouvé');
            header('Location: /pilotes/etudiants');
            return;
        }

        // Récupérer les données du formulaire
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Valider le mot de passe
        if (empty($password) || $password !== $confirmPassword) {
            $this->addFlashMessage('error', 'Les mots de passe ne correspondent pas ou sont vides');
            header('Location: /pilotes/etudiants/' . $etudiantId . '/password');
            return;
        }

        // Récupérer l'ID utilisateur de l'étudiant
        $student = $this->studentModel->getStudentInfo($etudiantId);

        if (!$student) {
            $this->addFlashMessage('error', 'Étudiant non trouvé');
            header('Location: /pilotes/etudiants');
            return;
        }

        // Mettre à jour le mot de passe
        $updatePassword = $this->pilotModel->updateStudentPassword($student['Id_Utilisateur'], $password);

        if ($updatePassword) {
            $this->addFlashMessage('success', 'Mot de passe mis à jour avec succès');
            header('Location: /pilotes/etudiants/' . $etudiantId);
        } else {
            $this->addFlashMessage('error', 'Erreur lors de la mise à jour du mot de passe');
            header('Location: /pilotes/etudiants/' . $etudiantId . '/password');
        }
    }

    public function etudiantSupprimer($params) {
        $this->requirePilote();

        $etudiantId = $params['id'] ?? null;

        if (!$etudiantId) {
            $this->addFlashMessage('error', 'Étudiant non trouvé');
            header('Location: /pilotes/etudiants');
            return;
        }

        // Récupérer les détails de l'étudiant
        $student = $this->studentModel->getStudentInfo($etudiantId);

        if (!$student) {
            $this->addFlashMessage('error', 'Étudiant non trouvé');
            header('Location: /pilotes/etudiants');
            return;
        }

        // Générer le token CSRF pour le formulaire
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        echo $this->twig->render('pilotes/etudiants/delete.html.twig', [
            'pilotePage' => true,
            'student' => $student,
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    }

    public function etudiantSupprimerValider($params) {
        $this->requirePilote();

        $etudiantId = $params['id'] ?? null;

        if (!$etudiantId) {
            $this->addFlashMessage('error', 'Étudiant non trouvé');
            header('Location: /pilotes/etudiants');
            return;
        }

        // Récupérer l'ID utilisateur de l'étudiant
        $student = $this->studentModel->getStudentInfo($etudiantId);

        if (!$student) {
            $this->addFlashMessage('error', 'Étudiant non trouvé');
            header('Location: /pilotes/etudiants');
            return;
        }

        // Supprimer l'étudiant (d'abord de la table etudiant puis de la table utilisateur)
        $success = $this->studentModel->delete($etudiantId);

        if ($success) {
            // Supprimer l'utilisateur associé
            $this->userModel->delete($student['Id_Utilisateur']);
            $this->addFlashMessage('success', 'Étudiant supprimé avec succès');
        } else {
            $this->addFlashMessage('error', 'Erreur lors de la suppression de l\'étudiant');
        }

        header('Location: /pilotes/etudiants');
    }

    public function etudiantWishlist($params) {
        $this->requirePilote();

        $etudiantId = $params['id'] ?? null;

        if (!$etudiantId) {
            $this->addFlashMessage('error', 'Étudiant non trouvé');
            header('Location: /pilotes/etudiants');
            return;
        }

        // Récupérer les détails de l'étudiant
        $student = $this->studentModel->getStudentInfo($etudiantId);

        if (!$student) {
            $this->addFlashMessage('error', 'Étudiant non trouvé');
            header('Location: /pilotes/etudiants');
            return;
        }

        // Récupérer la wishlist de l'étudiant
        $wishlist = $this->studentModel->getStudentWishlist($etudiantId);

        echo $this->twig->render('pilotes/etudiants/wishlist.html.twig', [
            'pilotePage' => true,
            'student' => $student,
            'offers' => $wishlist
        ]);
    }

    public function etudiantOffres($params) {
        $this->requirePilote();

        $etudiantId = $params['id'] ?? null;

        if (!$etudiantId) {
            $this->addFlashMessage('error', 'Étudiant non trouvé');
            header('Location: /pilotes/etudiants');
            return;
        }

        // Récupérer les détails de l'étudiant
        $student = $this->studentModel->getStudentInfo($etudiantId);

        if (!$student) {
            $this->addFlashMessage('error', 'Étudiant non trouvé');
            header('Location: /pilotes/etudiants');
            return;
        }

        // Récupérer les candidatures de l'étudiant
        $applications = $this->studentModel->getStudentApplications($etudiantId);

        echo $this->twig->render('pilotes/etudiants/offres.html.twig', [
            'pilotePage' => true,
            'student' => $student,
            'offers' => $applications
        ]);
    }

    // Gestion des entreprises
    public function entreprises() {
        $this->requirePilote();

        // Récupérer les entreprises
        $enterprises = $this->enterpriseModel->getEnterprisesByName();

        echo $this->twig->render('pilotes/entreprises/index.html.twig', [
            'pilotePage' => true,
            'enterprises' => $enterprises
        ]);
    }

    /**
     * Recherche d'entreprises
     */
    public function rechercheEntreprise() {
        $this->requirePilote();

        $searchTerm = $_POST['search'] ?? '';

        if (empty($searchTerm)) {
            // Si aucun terme de recherche, rediriger vers la liste complète
            header('Location: /pilotes/entreprises');
            exit;
        }

        // Effectuer la recherche
        $enterprises = $this->pilotModel->searchEnterprises($searchTerm);

        echo $this->twig->render('pilotes/entreprises/index.html.twig', [
            'pilotePage' => true,
            'enterprises' => $enterprises,
            'searchTerm' => $searchTerm
        ]);
    }

    public function entrepriseDetails($params) {
        $this->requirePilote();

        $enterpriseId = $params['id'] ?? null;

        if (!$enterpriseId) {
            $this->addFlashMessage('error', 'Entreprise non trouvée');
            header('Location: /pilotes/entreprises');
            return;
        }

        // Récupérer les détails de l'entreprise
        $enterprise = $this->enterpriseModel->getEnterpriseDetails($enterpriseId);

        if (!$enterprise) {
            $this->addFlashMessage('error', 'Entreprise non trouvée');
            header('Location: /pilotes/entreprises');
            return;
        }

        echo $this->twig->render('pilotes/entreprises/show.html.twig', [
            'pilotePage' => true,
            'enterprise' => $enterprise
        ]);
    }

    /**
     * Affiche les offres d'une entreprise
     */
    public function entrepriseOffres($params) {
        $this->requirePilote();

        $enterpriseId = $params['id'] ?? null;

        if (!$enterpriseId) {
            $this->addFlashMessage('error', 'Entreprise non trouvée');
            header('Location: /pilotes/entreprises');
            return;
        }

        // Récupérer les détails de l'entreprise
        $enterprise = $this->enterpriseModel->getEnterpriseDetails($enterpriseId);

        if (!$enterprise) {
            $this->addFlashMessage('error', 'Entreprise non trouvée');
            header('Location: /pilotes/entreprises');
            return;
        }

        // Récupérer les offres de l'entreprise
        $offers = $this->pilotModel->getEnterpriseOffers($enterpriseId);

        echo $this->twig->render('pilotes/entreprises/offres.html.twig', [
            'pilotePage' => true,
            'enterprise' => $enterprise,
            'offers' => $offers
        ]);
    }

    /**
     * Traite l'évaluation d'une entreprise
     */
    public function rateEnterprise($params) {
        $this->requirePilote();

        $enterpriseId = $params['id'] ?? null;

        if (!$enterpriseId) {
            $this->addFlashMessage('error', 'Entreprise non trouvée');
            header('Location: /pilotes/entreprises');
            return;
        }

        // Récupérer la note
        $rating = (int)($_POST['rating'] ?? 0);

        // Valider la note (entre 1 et 5)
        if ($rating < 1 || $rating > 5) {
            $this->addFlashMessage('error', 'Note invalide (doit être entre 1 et 5)');
            header('Location: /pilotes/entreprises/' . $enterpriseId);
            return;
        }

        // Récupérer l'ID utilisateur du pilote
        $userId = $_SESSION['user_id'];

        // Enregistrer l'évaluation
        $success = $this->pilotModel->rateEnterprise($enterpriseId, $userId, $rating);

        if ($success) {
            $this->addFlashMessage('success', 'Évaluation enregistrée avec succès');
        } else {
            $this->addFlashMessage('error', 'Erreur lors de l\'enregistrement de l\'évaluation');
        }

        header('Location: /pilotes/entreprises/' . $enterpriseId);
    }

    // Méthodes pour ajouter, modifier et supprimer des entreprises
    public function ajouterEntreprise() {
        $this->requirePilote();
        echo $this->twig->render('pilotes/entreprises/ajouter.html.twig', [
            'pilotePage' => true
        ]);
    }

    public function enregistrerEntreprise() {
        $this->requirePilote();

        // Récupérer les données du formulaire
        $nom = $_POST['nom'] ?? '';
        $description = $_POST['description'] ?? '';
        $email = $_POST['email'] ?? '';
        $telephone = $_POST['telephone'] ?? '';
        $effectif = (int)($_POST['effectif'] ?? 0);
        $ville = $_POST['ville'] ?? '';
        $codePostal = (int)($_POST['code_postal'] ?? 0);
        $adresse = $_POST['adresse'] ?? '';
        $secteurs = $_POST['secteurs'] ?? [];

        // Validation des données
        if (empty($nom) || empty($email) || empty($ville) || empty($codePostal)) {
            $this->addFlashMessage('error', 'Veuillez remplir tous les champs obligatoires');
            header('Location: /pilotes/entreprises/ajouter');
            return;
        }

        // Créer l'entreprise
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

        $enterpriseId = $this->enterpriseModel->createEnterprise($enterpriseData, $secteurs);

        if ($enterpriseId) {
            $this->addFlashMessage('success', 'Entreprise ajoutée avec succès');
            header('Location: /pilotes/entreprises/' . $enterpriseId);
        } else {
            $this->addFlashMessage('error', 'Erreur lors de l\'ajout de l\'entreprise');
            header('Location: /pilotes/entreprises/ajouter');
        }
    }

    public function modifierEntreprise($params) {
        $this->requirePilote();

        $enterpriseId = $params['id'] ?? null;

        if (!$enterpriseId) {
            $this->addFlashMessage('error', 'Entreprise non trouvée');
            header('Location: /pilotes/entreprises');
            return;
        }

        // Récupérer les détails de l'entreprise
        $enterprise = $this->enterpriseModel->getEnterpriseDetails($enterpriseId);

        if (!$enterprise) {
            $this->addFlashMessage('error', 'Entreprise non trouvée');
            header('Location: /pilotes/entreprises');
            return;
        }

        echo $this->twig->render('pilotes/entreprises/modifier.html.twig', [
            'pilotePage' => true,
            'enterprise' => $enterprise
        ]);
    }

    public function mettreAJourEntreprise($params) {
        $this->requirePilote();

        $enterpriseId = $params['id'] ?? null;

        if (!$enterpriseId) {
            $this->addFlashMessage('error', 'Entreprise non trouvée');
            header('Location: /pilotes/entreprises');
            return;
        }

        // Récupérer les données du formulaire
        $nom = $_POST['nom'] ?? '';
        $description = $_POST['description'] ?? '';
        $email = $_POST['email'] ?? '';
        $telephone = $_POST['telephone'] ?? '';
        $effectif = (int)($_POST['effectif'] ?? 0);
        $ville = $_POST['ville'] ?? '';
        $codePostal = (int)($_POST['code_postal'] ?? 0);
        $adresse = $_POST['adresse'] ?? '';
        $secteurs = $_POST['secteurs'] ?? [];

        // Validation des données
        if (empty($nom) || empty($email) || empty($ville) || empty($codePostal)) {
            $this->addFlashMessage('error', 'Veuillez remplir tous les champs obligatoires');
            header('Location: /pilotes/entreprises/' . $enterpriseId . '/modifier');
            return;
        }

        // Mettre à jour l'entreprise
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

        $success = $this->enterpriseModel->updateEnterprise($enterpriseId, $enterpriseData, $secteurs);

        if ($success) {
            $this->addFlashMessage('success', 'Entreprise mise à jour avec succès');
            header('Location: /pilotes/entreprises/' . $enterpriseId);
        } else {
            $this->addFlashMessage('error', 'Erreur lors de la mise à jour de l\'entreprise');
            header('Location: /pilotes/entreprises/' . $enterpriseId . '/modifier');
        }
    }

    public function entrepriseSupprimer($params) {
        $this->requirePilote();

        $enterpriseId = $params['id'] ?? null;

        if (!$enterpriseId) {
            $this->addFlashMessage('error', 'Entreprise non trouvée');
            header('Location: /pilotes/entreprises');
            return;
        }

        // Vérifier si l'entreprise existe
        $enterprise = $this->enterpriseModel->getEnterpriseDetails($enterpriseId);

        if (!$enterprise) {
            $this->addFlashMessage('error', 'Entreprise non trouvée');
            header('Location: /pilotes/entreprises');
            return;
        }

        // Supprimer l'entreprise
        $success = $this->enterpriseModel->delete($enterpriseId);

        if ($success) {
            $this->addFlashMessage('success', 'Entreprise supprimée avec succès');
        } else {
            $this->addFlashMessage('error', 'Erreur lors de la suppression de l\'entreprise');
        }

        header('Location: /pilotes/entreprises');
    }

    // Gestion des offres
    public function offres() {
        $this->requirePilote();

        // Récupérer les offres
        $offers = $this->offerModel->getRecentOffers();

        echo $this->twig->render('pilotes/offres/index.html.twig', [
            'pilotePage' => true,
            'offers' => $offers
        ]);
    }

    /**
     * Recherche d'offres
     */
    public function rechercheOffre() {
        $this->requirePilote();

        $searchTerm = $_POST['search'] ?? '';

        if (empty($searchTerm)) {
            // Si aucun terme de recherche, rediriger vers la liste complète
            header('Location: /pilotes/offres');
            exit;
        }

        // Effectuer la recherche
        $offers = $this->pilotModel->searchOffers($searchTerm);

        echo $this->twig->render('pilotes/offres/index.html.twig', [
            'pilotePage' => true,
            'offers' => $offers,
            'searchTerm' => $searchTerm
        ]);
    }

    public function offreDetails($params) {
        $this->requirePilote();

        $offerId = $params['id'] ?? null;

        if (!$offerId) {
            $this->addFlashMessage('error', 'Offre non trouvée');
            header('Location: /pilotes/offres');
            return;
        }

        // Récupérer les détails de l'offre
        $offer = $this->offerModel->getOfferDetails($offerId);

        if (!$offer) {
            $this->addFlashMessage('error', 'Offre non trouvée');
            header('Location: /pilotes/offres');
            return;
        }

        echo $this->twig->render('pilotes/offres/show.html.twig', [
            'pilotePage' => true,
            'offer' => $offer
        ]);
    }

    // Méthodes pour ajouter, modifier et supprimer des offres
    public function ajouterOffre() {
        $this->requirePilote();

        // Récupérer la liste des entreprises pour le formulaire
        $enterprises = $this->enterpriseModel->getAll('Nom_Entreprise');
        $competences = $this->offerModel->getAllCompetences();

        // Générer le token CSRF pour le formulaire
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        echo $this->twig->render('pilotes/offres/add.html.twig', [
            'pilotePage' => true,
            'enterprises' => $enterprises,
            'competences' => $competences,
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    }

    public function enregistrerOffre() {
        $this->requirePilote();

        // Récupérer les données du formulaire
        $titre = $_POST['titre'] ?? '';
        $description = $_POST['description'] ?? '';
        $remuneration = (int)($_POST['remuneration'] ?? 0);
        $niveauRequis = $_POST['niveau_requis'] ?? '';
        $dateDebut = $_POST['date_debut'] ?? '';
        $dureeMin = (int)($_POST['duree_min'] ?? 0);
        $dureeMax = (int)($_POST['duree_max'] ?? 0);
        $idEntreprise = (int)($_POST['id_entreprise'] ?? 0);
        $competences = $_POST['competences'] ?? [];

        // Validation des données
        if (empty($titre) || empty($dateDebut) || $idEntreprise <= 0) {
            $this->addFlashMessage('error', 'Veuillez remplir tous les champs obligatoires');
            header('Location: /pilotes/offres/ajouter');
            return;
        }

        // Créer l'offre
        $offerData = [
            'titre' => $titre,
            'description' => $description,
            'remuneration' => $remuneration,
            'niveauRequis' => $niveauRequis,
            'dateDebut' => $dateDebut,
            'dureeMin' => $dureeMin,
            'dureeMax' => $dureeMax,
            'idEntreprise' => $idEntreprise
        ];

        $offerId = $this->offerModel->createOffer($offerData, $competences);

        if ($offerId) {
            $this->addFlashMessage('success', 'Offre ajoutée avec succès');
            header('Location: /pilotes/offres/' . $offerId);
        } else {
            $this->addFlashMessage('error', 'Erreur lors de l\'ajout de l\'offre');
            header('Location: /pilotes/offres/ajouter');
        }
    }

    public function modifierOffre($params) {
        $this->requirePilote();

        $offerId = $params['id'] ?? null;

        if (!$offerId) {
            $this->addFlashMessage('error', 'Offre non trouvée');
            header('Location: /pilotes/offres');
            return;
        }

        $offer = $this->offerModel->getOfferDetails($offerId);
        $enterprises = $this->enterpriseModel->getAll('Nom_Entreprise');
        $competences = $this->offerModel->getAllCompetences(); // Utilisation de notre nouvelle méthode

        // Préparation des compétences sélectionnées pour faciliter l'affichage
        $selectedCompetences = array_map(function($skill) {
            return $skill['Id_Competence'];
        }, $offer['skills'] ?? []);

        // Générer le token CSRF pour le formulaire
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        echo $this->twig->render('pilotes/offres/edit.html.twig', [
            'pilotePage' => true,
            'offer' => $offer,
            'enterprises' => $enterprises,
            'competences' => $competences,
            'selected_competences' => $selectedCompetences,
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    }

    public function mettreAJourOffre($params) {
        $this->requirePilote();

        $offerId = $params['id'] ?? null;

        if (!$offerId) {
            $this->addFlashMessage('error', 'Offre non trouvée');
            header('Location: /pilotes/offres');
            return;
        }

        // Récupérer les données du formulaire
        $titre = $_POST['titre'] ?? '';
        $description = $_POST['description'] ?? '';
        $remuneration = (int)($_POST['remuneration'] ?? 0);
        $niveauRequis = $_POST['niveau_requis'] ?? '';
        $dateDebut = $_POST['date_debut'] ?? '';
        $dureeMin = (int)($_POST['duree_min'] ?? 0);
        $dureeMax = (int)($_POST['duree_max'] ?? 0);
        $idEntreprise = (int)($_POST['id_entreprise'] ?? 0);
        $competences = $_POST['competences'] ?? [];

        // Validation des données
        if (empty($titre) || empty($dateDebut) || $idEntreprise <= 0) {
            $this->addFlashMessage('error', 'Veuillez remplir tous les champs obligatoires');
            header('Location: /pilotes/offres/' . $offerId . '/modifier');
            return;
        }

        // Mettre à jour l'offre
        $offerData = [
            'titre' => $titre,
            'description' => $description,
            'remuneration' => $remuneration,
            'niveauRequis' => $niveauRequis,
            'dateDebut' => $dateDebut,
            'dureeMin' => $dureeMin,
            'dureeMax' => $dureeMax,
            'idEntreprise' => $idEntreprise
        ];

        $success = $this->offerModel->updateOffer($offerId, $offerData, $competences);

        if ($success) {
            $this->addFlashMessage('success', 'Offre mise à jour avec succès');
            header('Location: /pilotes/offres/' . $offerId);
        } else {
            $this->addFlashMessage('error', 'Erreur lors de la mise à jour de l\'offre');
            header('Location: /pilotes/offres/' . $offerId . '/modifier');
        }
    }

    /**
     * Affiche la page de confirmation de suppression d'une offre
     *
     * @param array $params Paramètres de la route
     */
    public function afficherSupprimerOffre($params) {
        $this->requirePilote();

        $offerId = $params['id'] ?? null;

        if (!$offerId) {
            $this->addFlashMessage('error', 'Offre non trouvée');
            header('Location: /pilotes/offres');
            return;
        }

        // Récupérer les détails de l'offre
        $offer = $this->offerModel->getOfferDetails($offerId);

        if (!$offer) {
            $this->addFlashMessage('error', 'Offre non trouvée');
            header('Location: /pilotes/offres');
            return;
        }

        // Générer le token CSRF pour le formulaire
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        echo $this->twig->render('pilotes/offres/delete.html.twig', [
            'pilotePage' => true,
            'offer' => $offer,
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    }

    public function supprimerOffre($params) {
        $this->requirePilote();

        $offerId = $params['id'] ?? null;

        if (!$offerId) {
            $this->addFlashMessage('error', 'Offre non trouvée');
            header('Location: /pilotes/offres');
            return;
        }

        // Vérifier si l'offre existe
        $offer = $this->offerModel->getOfferDetails($offerId);

        if (!$offer) {
            $this->addFlashMessage('error', 'Offre non trouvée');
            header('Location: /pilotes/offres');
            return;
        }

        // Supprimer l'offre
        $success = $this->offerModel->deleteOffer($offerId);

        if ($success) {
            $this->addFlashMessage('success', 'Offre supprimée avec succès');
        } else {
            $this->addFlashMessage('error', 'Erreur lors de la suppression de l\'offre');
        }

        header('Location: /pilotes/offres');
    }
}