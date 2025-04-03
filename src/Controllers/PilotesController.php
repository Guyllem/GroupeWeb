<?php
namespace App\Controllers;

use App\Models\CampusModel;
use App\Models\PilotModel;
use App\Models\PromotionModel;
use App\Models\StudentModel;
use App\Models\EnterpriseModel;
use App\Models\OfferModel;
use App\Models\UserModel;
use App\Utils\SecurityUtil;

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
        $this->render('pilotes/index.html.twig');
    }

    // Gestion des étudiants
    public function etudiants() {
        $this->requirePilote();

        // Récupérer le pilote actuel
        $userId = $_SESSION['user_id'];
        $pilotId = $this->pilotModel->getPilotIdFromUserId($userId);

        // Récupérer les étudiants supervisés par ce pilote
        $students = $this->pilotModel->getSupervisedStudents($pilotId);

        $this->render('pilotes/etudiants/index.html.twig', [
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

        $this->render('pilotes/etudiants/index.html.twig', [
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

        $this->render('pilotes/etudiants/show.html.twig', [
            'pilotePage' => true,
            'student' => $student,
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    }

    public function ajouterEtudiant() {
        $this->requirePilote();

        // Récupérer le pilote actuel
        $userId = $_SESSION['user_id'];
        $pilotId = $this->pilotModel->getPilotIdFromUserId($userId);

        if (!$pilotId) {
            $this->addFlashMessage('error', 'Erreur d\'identification du pilote');
            header('Location: /pilotes');
            exit;
        }

        // Instancier les modèles de données
        $campusModel = new CampusModel($this->db);

        // Récupérer uniquement les promotions supervisées par ce pilote
        $promotions = $this->pilotModel->getSupervisedPromotions($pilotId);

        // Récupérer tous les campus (ou filtrer également selon les campus des promotions)
        $campus = $campusModel->getAllCampus();

        // Générer le token CSRF pour le formulaire
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $this->render('pilotes/etudiants/add.html.twig', [
            'pilotePage' => true,
            'csrf_token' => $_SESSION['csrf_token'],
            'promotions' => $promotions,
            'campus' => $campus
        ]);
    }

    /**
     * Traite les données du formulaire d'ajout d'étudiant et crée les entrées nécessaires
     * dans la base de données via une transaction atomique unifiée.
     *
     * @return void
     */
    public function enregistrerEtudiant() {
        $this->requirePilote();

        // Récupération de l'ID du pilote
        $userId = $_SESSION['user_id'];
        $pilotId = $this->pilotModel->getPilotIdFromUserId($userId);

        if (!$pilotId) {
            $this->addFlashMessage('error', 'Identification du pilote impossible');
            header('Location: /pilotes/etudiants/');
            exit;
        }

        // Vérification du token CSRF
        if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) ||
            $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $this->addFlashMessage('error', 'Erreur de sécurité. Veuillez réessayer.');
            header('Location: /pilotes/etudiants/');
            exit;
        }

        // Récupération et assainissement des données du formulaire
        $nom = SecurityUtil::sanitizeInput($_POST['nom'] ?? '');
        $prenom = SecurityUtil::sanitizeInput($_POST['prenom'] ?? '');
        $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';
        $promotionId = (int)($_POST['promotion'] ?? 0);
        $campusId = (int)($_POST['campus'] ?? 0);
        $telephone = SecurityUtil::sanitizeInput($_POST['telephone'] ?? '');

        // Validation des données
        if (empty($nom) || empty($prenom) || empty($email) || empty($password) ||
            empty($promotionId) || empty($campusId)) {
            $this->addFlashMessage('error', 'Veuillez remplir tous les champs obligatoires');
            header('Location: /pilotes/etudiants/ajouter');
            exit;
        }

        try {
            $conn = $this->db->connect();

            // NOUVELLE VÉRIFICATION: S'assurer que le pilote supervise bien cette promotion
            $stmt = $conn->prepare('
            SELECT COUNT(*) FROM Superviser 
            WHERE Id_Pilote = :pilotId AND Id_Promotion = :promotionId
        ');
            $stmt->bindParam(':pilotId', $pilotId);
            $stmt->bindParam(':promotionId', $promotionId);
            $stmt->execute();

            if ($stmt->fetchColumn() == 0) {
                $this->addFlashMessage('error', 'Vous n\'êtes pas autorisé à ajouter un étudiant à cette promotion');
                header('Location: /pilotes/etudiants/ajouter');
                exit;
            }

            // Vérification préalable de l'unicité de l'email
            $stmt = $conn->prepare('SELECT COUNT(*) FROM Utilisateur WHERE Email_Utilisateur = :email');
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            if ($stmt->fetchColumn() > 0) {
                $this->addFlashMessage('error', 'Cet email est déjà utilisé');
                header('Location: /pilotes/etudiants/ajouter');
                exit;
            }

            // Création de l'étudiant via la méthode transactionnelle unifiée
            $result = $this->userModel->createStudentWithUser($email, $password, $nom, $prenom);

            if (!$result || !isset($result['userId']) || !isset($result['studentId'])) {
                throw new \Exception("Erreur lors de la création de l'utilisateur et de l'étudiant");
            }

            $userId = $result['userId'];
            $studentId = $result['studentId'];

            $this->userModel->updateUserPhone($userId, $telephone);

            // Journal de débogage
            error_log("Utilisateur/Étudiant créé avec succès - ID Utilisateur: $userId, ID Étudiant: $studentId");

            // Débuter une nouvelle transaction pour les opérations complémentaires
            $conn->beginTransaction();

            try {
                // Association de l'étudiant à la promotion
                $dateDebut = date('Y-m-d');
                $dateFin = date('Y-m-d', strtotime('+2 years'));

                $stmt = $conn->prepare('
                INSERT INTO Appartenir (
                    Id_Etudiant, 
                    Id_Promotion, 
                    Date_Debut_Appartenir, 
                    Date_Fin_Appartenir
                ) VALUES (
                    :studentId, 
                    :promotionId, 
                    :dateDebut, 
                    :dateFin
                )
            ');
                $stmt->bindParam(':studentId', $studentId);
                $stmt->bindParam(':promotionId', $promotionId);
                $stmt->bindParam(':dateDebut', $dateDebut);
                $stmt->bindParam(':dateFin', $dateFin);
                $stmt->execute();

                // Validation des opérations complémentaires
                $conn->commit();

                // Ajout du message de succès et redirection
                $this->addFlashMessage('success', 'Étudiant ajouté avec succès');

                // Garantir que la session est écrite avant redirection
                session_write_close();

                header('Location: /pilotes/etudiants');
                exit;

            } catch (\Exception $e) {
                // Annulation des opérations complémentaires en cas d'erreur
                $conn->rollBack();
                throw $e; // Propager l'exception pour la gestion globale
            }

        } catch (\Exception $e) {
            error_log("Erreur lors de l'enregistrement de l'étudiant: " . $e->getMessage());

            $this->addFlashMessage('error', 'Erreur lors de l\'ajout de l\'étudiant: ' . $e->getMessage());
            header('Location: /pilotes/etudiants/ajouter');
            exit;
        }
    }

    public function modifierEtudiant($params) {
        $this->requirePilote();

        // Récupérer le pilote actuel
        $userId = $_SESSION['user_id'];
        $pilotId = $this->pilotModel->getPilotIdFromUserId($userId);

        if (!$pilotId) {
            $this->addFlashMessage('error', 'Erreur d\'identification du pilote');
            header('Location: /pilotes');
            exit;
        }

        $etudiantId = $params['id'] ?? null;

        if (!$etudiantId) {
            $this->addFlashMessage('error', 'Étudiant non trouvé');
            header('Location: /pilotes/etudiants');
            return;
        }

        // Récupérer les données complètes de l'étudiant via le modèle
        $student = $this->studentModel->getStudentAcademicInfo($etudiantId);

        if (!$student) {
            $this->addFlashMessage('error', 'Étudiant non trouvé');
            header('Location: /pilotes/etudiants');
            return;
        }

        // Instancier les modèles de données
        $campusModel = new CampusModel($this->db);

        // Récupérer uniquement les promotions supervisées par ce pilote
        $promotions = $this->pilotModel->getSupervisedPromotions($pilotId);

        // Récupérer tous les campus (ou filtrer également selon les campus des promotions)
        $campus = $campusModel->getAllCampus();

        // Générer le token CSRF pour le formulaire
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        // Récupérer les compétences de l'étudiant
        $student['skills'] = $this->studentModel->getStudentSkills($etudiantId);

        $this->render('pilotes/etudiants/edit.html.twig', [
            'pilotePage' => true,
            'student' => $student,
            'csrf_token' => $_SESSION['csrf_token'],
            'promotions' => $promotions,
            'campus' => $campus
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
        $promotionId = $_POST['promotion'] ?? null;
        $telephone = $_POST['telephone'] ?? '';
        $skills = isset($_POST['skills']) ? explode(',', $_POST['skills']) : [];

        // Validation des données
        if (empty($nom) || empty($prenom) || empty($email)) {
            $this->addFlashMessage('error', 'Veuillez remplir tous les champs obligatoires');
            header('Location: /pilotes/etudiants/' . $etudiantId . '/modifier');
            return;
        }

        // Récupérer l'étudiant actuel
        $student = $this->studentModel->getStudentInfo($etudiantId);

        if (!$student) {
            $this->addFlashMessage('error', 'Étudiant non trouvé');
            header('Location: /pilotes/etudiants');
            return;
        }

        // Création d'un tableau pour suivre les opérations
        $operations = [];

        // 1. Mettre à jour les informations de base via le modèle utilisateur
        $updateUser = $this->userModel->updateUser(
            $student['Id_Utilisateur'],
            $email,
            null, // pas de changement de mot de passe ici
            $nom,
            $prenom
        );
        $operations[] = $updateUser ? 'Informations de base mises à jour' : 'Échec de la mise à jour des informations de base';

        // 2. Mettre à jour le téléphone via le modèle utilisateur
        $updatePhone = $this->userModel->updateUserPhone($student['Id_Utilisateur'], $telephone);
        $operations[] = $updatePhone ? 'Téléphone mis à jour' : 'Échec de la mise à jour du téléphone';

        // 3. Mettre à jour la promotion via le modèle étudiant
        if (!empty($promotionId)) {
            $updatePromotion = $this->studentModel->updateStudentPromotion($etudiantId, $promotionId);
            $operations[] = $updatePromotion ? 'Promotion mise à jour' : 'Échec de la mise à jour de la promotion';
        }

        // 4. Mettre à jour les compétences via le modèle étudiant
        $updateSkills = $this->studentModel->updateStudentSkills($etudiantId, $skills);
        $operations[] = $updateSkills ? 'Compétences mises à jour' : 'Échec de la mise à jour des compétences';

        // Déterminer si toutes les opérations ont réussi
        $success = !in_array(false, [$updateUser, $updatePhone, $updatePromotion ?? true, $updateSkills]);

        if ($success) {
            $this->addFlashMessage('success', 'Étudiant mis à jour avec succès');
        } else {
            // Log des opérations pour le débogage
            error_log('Mises à jour étudiant, résultats: ' . implode(', ', $operations));
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

        $this->render('pilotes/etudiants/password.html.twig', [
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

        $this->render('pilotes/etudiants/delete.html.twig', [
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

    /**
     * Affiche le formulaire de réinitialisation de mot de passe pour un étudiant
     *
     * @param array $params Paramètres de la route contenant l'ID de l'étudiant
     * @return void
     */
    public function afficherReset($params) {
        // Vérification des permissions
        $this->requirePilote();

        // Récupération et validation de l'ID étudiant
        $etudiantId = $params['id'] ?? null;
        if (!$etudiantId) {
            $this->addFlashMessage('error', 'Étudiant non trouvé');
            header('Location: /pilotes/etudiants');
            return;
        }

        // Récupération des données de l'étudiant
        $student = $this->studentModel->getStudentInfo($etudiantId);
        if (!$student) {
            $this->addFlashMessage('error', 'Données de l\'étudiant introuvables');
            header('Location: /pilotes/etudiants');
            return;
        }

        // Génération du token CSRF
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        // Rendu du template avec les données nécessaires
        $this->render('pilotes/etudiants/reset.html.twig', [
            'pilotePage' => true,
            'student' => $student,
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    }

    /**
     * Traite la soumission du formulaire de réinitialisation de mot de passe
     *
     * @param array $params Paramètres de la route contenant l'ID de l'étudiant
     * @return void
     */
    public function resetPassword($params) {
        // Vérification des permissions
        $this->requirePilote();

        // Récupération et validation de l'ID étudiant
        $etudiantId = $params['id'] ?? null;
        if (!$etudiantId) {
            $this->addFlashMessage('error', 'Étudiant non trouvé');
            header('Location: /pilotes/etudiants');
            return;
        }

        // Vérification du token CSRF
        if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) ||
            $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $this->addFlashMessage('error', 'Session expirée ou requête invalide. Veuillez réessayer.');
            header('Location: /pilotes/etudiants/' . $etudiantId . '/reset');
            return;
        }

        // Récupération et validation des données du formulaire
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validation des entrées
        if (empty($password)) {
            $this->addFlashMessage('error', 'Le mot de passe ne peut pas être vide');
            header('Location: /pilotes/etudiants/' . $etudiantId . '/reset');
            return;
        }

        if ($password !== $confirmPassword) {
            $this->addFlashMessage('error', 'Les mots de passe ne correspondent pas');
            header('Location: /pilotes/etudiants/' . $etudiantId . '/reset');
            return;
        }

        if (strlen($password) < 8) {
            $this->addFlashMessage('error', 'Le mot de passe doit contenir au moins 8 caractères');
            header('Location: /pilotes/etudiants/' . $etudiantId . '/reset');
            return;
        }

        // Récupération des informations de l'étudiant
        $student = $this->studentModel->getStudentInfo($etudiantId);
        if (!$student) {
            $this->addFlashMessage('error', 'Étudiant introuvable');
            header('Location: /pilotes/etudiants');
            return;
        }

        // Mise à jour du mot de passe
        $success = $this->pilotModel->updateStudentPassword($student['Id_Utilisateur'], $password);

        if ($success) {
            $this->addFlashMessage('success', 'Le mot de passe a été réinitialisé avec succès');
            header('Location: /pilotes/etudiants/' . $etudiantId);
        } else {
            $this->addFlashMessage('error', 'Une erreur s\'est produite lors de la réinitialisation du mot de passe');
            header('Location: /pilotes/etudiants/' . $etudiantId . '/reset');
        }
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

        $this->render('pilotes/etudiants/wishlist.html.twig', [
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

        $this->render('pilotes/etudiants/offres.html.twig', [
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

        $this->render('pilotes/entreprises/index.html.twig', [
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

        $this->render('pilotes/entreprises/index.html.twig', [
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

        $this->render('pilotes/entreprises/show.html.twig', [
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

        $this->render('pilotes/entreprises/offres.html.twig', [
            'pilotePage' => true,
            'enterprise' => $enterprise,
            'offers' => $offers
        ]);
    }

    /**
     * Affiche le formulaire d'évaluation d'une entreprise
     *
     * @param array $params Paramètres de la route
     */
    public function afficherRateEntreprise($params) {
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

        // Générer le token CSRF pour le formulaire
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $this->render('pilotes/entreprises/evaluer.html.twig', [
            'pilotePage' => true,
            'enterprise' => $enterprise,
            'csrf_token' => $_SESSION['csrf_token']
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

        // Générer le token CSRF pour le formulaire
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $this->requirePilote();
        $this->render('pilotes/entreprises/add.html.twig', [
            'pilotePage' => true,
            'csrf_token' => $_SESSION['csrf_token'],
        ]);
    }

    /**
     * Traite le formulaire d'ajout d'entreprise
     */
    public function enregistrerEntreprise() {
        $this->requirePilote();

        // Vérification CSRF
        if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) ||
            $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $this->addFlashMessage('error', 'Erreur de sécurité. Veuillez réessayer.');
            header('Location: /pilotes/entreprises/ajouter');
            return;
        }

        // Récupérer et nettoyer les données du formulaire
        $nom = SecurityUtil::sanitizeInput($_POST['nom'] ?? '');
        $description = SecurityUtil::sanitizeInput($_POST['description'] ?? '');
        $email = SecurityUtil::sanitizeInput($_POST['email'] ?? '');
        $telephone = SecurityUtil::sanitizeInput($_POST['telephone'] ?? '');
        $effectif = (int)($_POST['effectif'] ?? 0);
        $ville = SecurityUtil::sanitizeInput($_POST['ville'] ?? '');
        $codePostal = (int)($_POST['code_postal'] ?? 0);
        $adresse = SecurityUtil::sanitizeInput($_POST['adresse'] ?? '');
        $secteursCsv = SecurityUtil::sanitizeInput($_POST['secteurs'] ?? '');

        // Validation des données
        $errors = [];
        if (empty($nom)) {
            $errors['nom'] = 'Le nom de l\'entreprise est obligatoire';
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'L\'adresse email est invalide ou manquante';
        }
        if (empty($ville)) {
            $errors['ville'] = 'La ville est obligatoire';
        }
        if (empty($codePostal) || $codePostal <= 0) {
            $errors['code_postal'] = 'Le code postal est obligatoire et doit être un nombre positif';
        }

        // S'il y a des erreurs, retourner au formulaire avec les erreurs
        if (!empty($errors)) {
            $this->addFlashMessage('error', 'Veuillez corriger les erreurs dans le formulaire');

            // Stockage temporaire des données saisies pour repopulation du formulaire
            $_SESSION['form_data'] = [
                'nom' => $nom,
                'description' => $description,
                'email' => $email,
                'telephone' => $telephone,
                'effectif' => $effectif,
                'ville' => $ville,
                'code_postal' => $codePostal,
                'adresse' => $adresse,
                'secteurs' => $secteursCsv
            ];

            header('Location: /pilotes/entreprises/ajouter');
            return;
        }

        // Préparer les données pour la création de l'entreprise
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

        // Appel à la nouvelle méthode du modèle avec la chaîne CSV des secteurs
        $enterpriseId = $this->enterpriseModel->createEnterprise($enterpriseData, SecurityUtil::normalizeSectors($secteursCsv));

        if ($enterpriseId) {
            $this->addFlashMessage('success', 'Entreprise ajoutée avec succès');
            header('Location: /pilotes/entreprises/' . $enterpriseId);
        } else {
            $this->addFlashMessage('error', 'Erreur lors de l\'ajout de l\'entreprise. Veuillez réessayer.');
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

        // Générer le token CSRF pour le formulaire
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }


        $this->render('pilotes/entreprises/edit.html.twig', [
            'pilotePage' => true,
            'enterprise' => $enterprise,
            'csrf_token' => $_SESSION['csrf_token'],
        ]);
    }

    /**
     * Traite le formulaire de modification d'une entreprise
     *
     * @param array $params Paramètres de la route
     * @return void
     */
    public function mettreAJourEntreprise($params) {
        $this->requirePilote();

        $enterpriseId = $params['id'] ?? null;

        if (!$enterpriseId) {
            $this->addFlashMessage('error', 'Entreprise non trouvée');
            header('Location: /pilotes/entreprises');
            return;
        }

        // Vérification du token CSRF
        if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) ||
            $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $this->addFlashMessage('error', 'Erreur de sécurité. Veuillez réessayer.');
            header('Location: /pilotes/entreprises/' . $enterpriseId . '/modifier');
            return;
        }

        // Récupération et nettoyage des données du formulaire
        $nom = SecurityUtil::sanitizeInput($_POST['nom'] ?? '');
        $description = SecurityUtil::sanitizeInput($_POST['description'] ?? '');
        $email = SecurityUtil::sanitizeInput($_POST['email'] ?? '');
        $telephone = SecurityUtil::sanitizeInput($_POST['telephone'] ?? '');
        $effectif = (int)($_POST['effectif'] ?? 0);
        $ville = SecurityUtil::sanitizeInput($_POST['ville'] ?? '');
        $codePostal = (int)($_POST['code_postal'] ?? 0);
        $adresse = SecurityUtil::sanitizeInput($_POST['adresse'] ?? '');
        $secteursCsv = SecurityUtil::sanitizeInput($_POST['secteurs'] ?? '');

        // Validation des données - approche directe
        $errors = [];

        // Validation du nom
        if (empty(SecurityUtil::sanitizeInput($nom))) {
            $errors['nom'] = 'Le nom de l\'entreprise est obligatoire';
        }

        // Validation de l'email
        if (empty(SecurityUtil::sanitizeInput($email)) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'L\'adresse email est invalide ou manquante';
        }

        // Validation de la ville
        if (empty(SecurityUtil::sanitizeInput($ville))) {
            $errors['ville'] = 'La ville est obligatoire';
        }

        // Validation du code postal
        if (empty($codePostal) || $codePostal <= 0) {
            $errors['code_postal'] = 'Le code postal est obligatoire et doit être un nombre positif';
        }

        // Gestion des erreurs de validation
        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->addFlashMessage('error', $error);
            }

            // Stockage temporaire des données pour repopulation du formulaire
            $_SESSION['form_data'] = [
                'nom' => $nom,
                'description' => $description,
                'email' => $email,
                'telephone' => $telephone,
                'effectif' => $effectif,
                'ville' => $ville,
                'code_postal' => $codePostal,
                'adresse' => $adresse,
                'secteurs' => $secteursCsv
            ];

            header('Location: /pilotes/entreprises/' . $enterpriseId . '/modifier');
            return;
        }

        // Préparation des données pour la mise à jour
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

        // Appel à la méthode du modèle
        $success = $this->enterpriseModel->updateEnterprise($enterpriseId, $enterpriseData, $secteursCsv);

        // Gestion du résultat et redirection appropriée
        if ($success) {
            $this->addFlashMessage('success', 'Entreprise mise à jour avec succès');
            header('Location: /pilotes/entreprises/' . $enterpriseId);
        } else {
            $this->addFlashMessage('error', 'Erreur lors de la mise à jour de l\'entreprise');
            header('Location: /pilotes/entreprises/' . $enterpriseId . '/modifier');
        }
    }

    /**
     * Affiche la page de confirmation de suppression d'une entreprise
     *
     * @param array $params Paramètres de la route
     */
    public function afficherEntrepriseSupprimer($params) {
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

        // Générer le token CSRF pour le formulaire
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        // Rendre la vue de confirmation
        $this->render('pilotes/entreprises/delete.html.twig', [
            'pilotePage' => true,
            'enterprise' => $enterprise,
            'csrf_token' => $_SESSION['csrf_token']
        ]);
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

        $this->render('pilotes/offres/index.html.twig', [
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

        $this->render('pilotes/offres/index.html.twig', [
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

        $this->render('pilotes/offres/show.html.twig', [
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

        $this->render('pilotes/offres/add.html.twig', [
            'pilotePage' => true,
            'enterprises' => $enterprises,
            'competences' => $competences,
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    }

    /**
     * Traite le formulaire d'ajout d'offre avec assainissement des entrées
     */
    public function enregistrerOffre() {
        $this->requirePilote();

        // Vérification du token CSRF
        if (!isset($_POST['csrf_token']) || !SecurityUtil::verifyCsrfToken($_POST['csrf_token'])) {
            $this->addFlashMessage('error', 'Token de sécurité invalide');
            header('Location: /pilotes/offres/ajouter');
            return;
        }

        // Récupérer et assainir les données du formulaire
        $titre = SecurityUtil::sanitizeInput($_POST['titre'] ?? '');
        $description = SecurityUtil::sanitizeInput($_POST['description'] ?? '');
        $remuneration = filter_var($_POST['remuneration'] ?? 0, FILTER_VALIDATE_FLOAT);
        $niveauRequis = SecurityUtil::sanitizeInput($_POST['niveau_requis'] ?? '');
        $dateDebut = SecurityUtil::sanitizeInput($_POST['date_debut'] ?? '');
        $dureeMin = filter_var($_POST['duree_min'] ?? 0, FILTER_VALIDATE_INT);
        $dureeMax = filter_var($_POST['duree_max'] ?? 0, FILTER_VALIDATE_INT);
        $idEntreprise = filter_var($_POST['id_entreprise'] ?? 0, FILTER_VALIDATE_INT);

        // Pour les tableaux, assainir chaque élément individuellement
        $competences = [];
        if (isset($_POST['competences']) && is_array($_POST['competences'])) {
            foreach ($_POST['competences'] as $comp) {
                $competences[] = filter_var($comp, FILTER_VALIDATE_INT);
            }
        }

        // Validation des données
        $errors = [];

        if (empty($titre)) $errors['titre'] = 'Le titre est obligatoire';
        if (empty($description)) $errors['description'] = 'La description est obligatoire';
        if (empty($niveauRequis)) $errors['niveau_requis'] = 'Le niveau requis est obligatoire';
        if (empty($dateDebut)) $errors['date_debut'] = 'La date de début est obligatoire';
        if ($dureeMin <= 0) $errors['duree_min'] = 'La durée minimale doit être supérieure à 0';
        if ($dureeMax > 0 && $dureeMax < $dureeMin) $errors['duree_max'] = 'La durée maximale doit être supérieure à la durée minimale';
        if ($idEntreprise <= 0) $errors['id_entreprise'] = 'Veuillez sélectionner une entreprise';
        if (empty($competences)) $errors['competences'] = 'Veuillez sélectionner au moins une compétence';

        // Si des erreurs sont trouvées, rediriger vers le formulaire avec les erreurs
        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_data'] = $_POST; // Stocker les données originales pour remplir le formulaire
            $this->addFlashMessage('error', 'Veuillez corriger les erreurs dans le formulaire');
            header('Location: /pilotes/offres/ajouter');
            return;
        }

        // Préparation des données pour le modèle (déjà assainies)
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

        // Création de l'offre via le modèle
        $offerId = $this->offerModel->createOffer($offerData, $competences);

        if ($offerId) {
            $this->addFlashMessage('success', 'Offre ajoutée avec succès');
            header('Location: /pilotes/offres/' . $offerId);
        } else {
            $this->addFlashMessage('error', 'Erreur lors de l\'ajout de l\'offre. Veuillez réessayer.');
            header('Location: /pilotes/offres/ajouter');
        }
    }

    /**
     * Affiche le formulaire de modification d'une offre avec préchargement des données
     *
     * @param array $params Paramètres de route contenant l'ID de l'offre
     */
    public function modifierOffre($params) {
        $this->requirePilote();

        $offerId = filter_var($params['id'] ?? 0, FILTER_VALIDATE_INT);
        if (!$offerId) {
            $this->addFlashMessage('error', 'Identifiant d\'offre invalide');
            header('Location: /pilotes/offres');
            return;
        }

        // Récupération complète des données de l'offre avec jointures
        $offer = $this->offerModel->getOfferDetails($offerId);
        if (!$offer) {
            $this->addFlashMessage('error', 'L\'offre demandée n\'existe pas');
            header('Location: /pilotes/offres');
            return;
        }

        // Récupération de toutes les entreprises pour le dropdown
        $enterprises = $this->enterpriseModel->getAll('Nom_Entreprise');

        // Récupération de toutes les compétences disponibles
        $competences = $this->offerModel->getAllCompetences();

        // Extraction des IDs de compétences actuellement associées à l'offre
        $selectedCompetences = array_map(function($skill) {
            return $skill['Id_Competence'];
        }, $offer['skills'] ?? []);

        // Génération du token CSRF
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        // Priorité aux données en session en cas d'erreur de validation
        $formData = $_SESSION['form_data'] ?? null;
        unset($_SESSION['form_data']); // Nettoyage après utilisation

        // Si des données de formulaire sont présentes (après une erreur), les utiliser
        // Sinon, utiliser les données de l'offre existante
        $templateData = [
            'pilotePage' => true,
            'offer' => $offer,
            'enterprises' => $enterprises,
            'competences' => $competences,
            'selected_competences' => $selectedCompetences,
            'csrf_token' => $_SESSION['csrf_token'],
            'formData' => $formData // Pour repopuler le formulaire après erreur
        ];

        // Rendu du template avec toutes les données nécessaires
        echo $this->twig->render('pilotes/offres/edit.html.twig', $templateData);
    }

    /**
     * Traite le formulaire de modification d'une offre avec feedback précis via flash messages
     *
     * @param array $params Paramètres de la route (contient l'ID de l'offre)
     * @return void
     */
    public function mettreAJourOffre($params) {
        $this->requirePilote();

        // Validation de l'identifiant avec typage strict
        $offerId = filter_var($params['id'] ?? 0, FILTER_VALIDATE_INT);
        if (!$offerId) {
            $this->addFlashMessage('error', 'ID d\'offre invalide ou manquant dans les paramètres de route');
            header('Location: /pilotes/offres');
            return;
        }

        // Récupération de l'entité avec vérification d'existence
        $existingOffer = $this->offerModel->getOfferDetails($offerId);
        if (!$existingOffer) {
            $this->addFlashMessage('error', 'L\'offre #' . $offerId . ' n\'existe pas ou a été supprimée');
            header('Location: /pilotes/offres');
            return;
        }

        // Vérification CSRF avec contexte explicite
        if (!isset($_POST['csrf_token']) || !SecurityUtil::verifyCsrfToken($_POST['csrf_token'])) {
            $this->addFlashMessage('error', 'Token CSRF invalide - Possible tentative de falsification de requête ou session expirée');
            header('Location: /pilotes/offres/' . $offerId . '/modifier');
            return;
        }

        // Collecte et assainissement des données avec vérifications avancées
        $hasErrors = false;

        // Titre - Validation sémantique et sanitization
        $titre = SecurityUtil::sanitizeInput($_POST['titre'] ?? '');
        if (empty($titre)) {
            $this->addFlashMessage('error', 'Titre: Le champ est obligatoire');
            $hasErrors = true;
        } elseif (strlen($titre) < 3) {
            $this->addFlashMessage('error', 'Titre: Minimum 3 caractères requis (actuellement ' . strlen($titre) . ')');
            $hasErrors = true;
        } elseif (strlen($titre) > 100) {
            $this->addFlashMessage('error', 'Titre: Maximum 100 caractères autorisés (actuellement ' . strlen($titre) . ')');
            $hasErrors = true;
        }

        // Description - Contrôle de longueur et pertinence
        $description = SecurityUtil::sanitizeInput($_POST['description'] ?? '');
        if (empty($description)) {
            $this->addFlashMessage('error', 'Description: Le champ est obligatoire');
            $hasErrors = true;
        } elseif (strlen($description) < 30) {
            $this->addFlashMessage('error', 'Description: Minimum 30 caractères requis pour une description pertinente');
            $hasErrors = true;
        } elseif (strlen($description) > 1000) {
            $this->addFlashMessage('error', 'Description: Maximum 1000 caractères autorisés');
            $hasErrors = true;
        }

        // Rémunération - Validation numérique avec plages acceptables
        $remuneration = filter_var($_POST['remuneration'] ?? 0, FILTER_VALIDATE_FLOAT);
        if ($remuneration === false) {
            $this->addFlashMessage('error', 'Rémunération: Format numérique invalide');
            $hasErrors = true;
        } elseif ($remuneration < 0) {
            $this->addFlashMessage('error', 'Rémunération: La valeur ne peut pas être négative');
            $hasErrors = true;
        } elseif ($remuneration > 5000) {
            $this->addFlashMessage('warning', 'Rémunération: La valeur (' . $remuneration . '€) semble anormalement élevée pour un stage');
            // Warning ne bloque pas la validation
        }

        // Niveau requis - Validation par liste blanche
        $niveauRequis = SecurityUtil::sanitizeInput($_POST['niveau_requis'] ?? '');
        $niveauxAcceptes = ['Bac+1', 'Bac+2', 'Bac+3', 'Bac+4', 'Bac+5'];
        if (empty($niveauRequis)) {
            $this->addFlashMessage('error', 'Niveau requis: Le champ est obligatoire');
            $hasErrors = true;
        } elseif (!in_array($niveauRequis, $niveauxAcceptes)) {
            $this->addFlashMessage('error', 'Niveau requis: La valeur "' . $niveauRequis . '" n\'est pas acceptée (valeurs possibles: ' . implode(', ', $niveauxAcceptes) . ')');
            $hasErrors = true;
        }

        // Date de début - Validation calendaire avec contraintes temporelles
        $dateDebut = SecurityUtil::sanitizeInput($_POST['date_debut'] ?? '');
        if (empty($dateDebut)) {
            $this->addFlashMessage('error', 'Date de début: Le champ est obligatoire');
            $hasErrors = true;
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateDebut)) {
            $this->addFlashMessage('error', 'Date de début: Le format doit être AAAA-MM-JJ');
            $hasErrors = true;
        } else {
            try {
                $dateObj = new \DateTime($dateDebut);
                $now = new \DateTime();
                $now->setTime(0, 0, 0); // Normaliser pour comparaison par jour
                $futureLimit = new \DateTime('+2 years');

                if ($dateObj < $now) {
                    $this->addFlashMessage('error', 'Date de début: La date doit être ultérieure à aujourd\'hui');
                    $hasErrors = true;
                } elseif ($dateObj > $futureLimit) {
                    $this->addFlashMessage('error', 'Date de début: La date ne peut pas être à plus de 2 ans dans le futur');
                    $hasErrors = true;
                }
            } catch (\Exception $e) {
                $this->addFlashMessage('error', 'Date de début: Date invalide');
                $hasErrors = true;
            }
        }

        // Durées - Validation des bornes avec règles métier
        $dureeMin = filter_var($_POST['duree_min'] ?? 0, FILTER_VALIDATE_INT);
        if ($dureeMin === false || $dureeMin <= 0) {
            $this->addFlashMessage('error', 'Durée minimale: Doit être un nombre entier positif');
            $hasErrors = true;
        } elseif ($dureeMin > 52) {
            $this->addFlashMessage('error', 'Durée minimale: Ne peut pas dépasser 52 semaines (1 an)');
            $hasErrors = true;
        } elseif ($dureeMin < 1) {
            $this->addFlashMessage('error', 'Durée minimale: Ne peut pas être inférieure à 1 semaines');
            $hasErrors = true;
        }

        $dureeMax = filter_var($_POST['duree_max'] ?? 0, FILTER_VALIDATE_INT);
        if ($dureeMax !== false && $dureeMax > 0) {
            if ($dureeMax < $dureeMin) {
                $this->addFlashMessage('error', 'Durée maximale: Doit être supérieure ou égale à la durée minimale (' . $dureeMin . ' semaines)');
                $hasErrors = true;
            } elseif ($dureeMax > 52) {
                $this->addFlashMessage('error', 'Durée maximale: Ne peut pas dépasser 52 semaines (1 an)');
                $hasErrors = true;
            }
        } else {
            // Si non spécifiée, utiliser la durée minimale
            $dureeMax = $dureeMin;
        }

        // Entreprise - Validation avec vérification d'existence
        $idEntreprise = filter_var($_POST['id_entreprise'] ?? 0, FILTER_VALIDATE_INT);
        if (!$idEntreprise || $idEntreprise <= 0) {
            $this->addFlashMessage('error', 'Entreprise: Veuillez sélectionner une entreprise valide');
            $hasErrors = true;
        } else {
            // Vérification d'intégrité référentielle
            $entreprise = $this->enterpriseModel->getById($idEntreprise);
            if (!$entreprise) {
                $this->addFlashMessage('error', 'Entreprise: L\'entreprise sélectionnée (ID: ' . $idEntreprise . ') n\'existe pas');
                $hasErrors = true;
            }
        }

        // Compétences - Validation de présence et d'existence
        $competences = [];
        if (isset($_POST['competences']) && is_array($_POST['competences'])) {
            foreach ($_POST['competences'] as $comp) {
                $competenceId = filter_var($comp, FILTER_VALIDATE_INT);
                if ($competenceId && $competenceId > 0) {
                    $competences[] = $competenceId;
                }
            }
        }

        if (empty($competences)) {
            $this->addFlashMessage('error', 'Compétences: Sélectionnez au moins une compétence requise');
            $hasErrors = true;
        } else {
            // Vérification d'intégrité référentielle des compétences
            $allCompetences = $this->offerModel->getAllCompetences();
            $validIds = array_column($allCompetences, 'Id_Competence');
            $invalidIds = array_diff($competences, $validIds);

            if (!empty($invalidIds)) {
                $this->addFlashMessage('error', 'Compétences: Certaines compétences sélectionnées sont invalides: ' . implode(', ', $invalidIds));
                $hasErrors = true;
            }
        }

        // Gestion d'état - En cas d'erreurs, rediriger vers le formulaire
        if ($hasErrors) {
            // Conserver les données pour remplir le formulaire
            $_SESSION['form_data'] = $_POST;
            header('Location: /pilotes/offres/' . $offerId . '/modifier');
            return;
        }

        // Préparation des données pour le modèle en cas de validation réussie
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

        // Mise à jour via le modèle avec gestion d'erreurs contextuelle
        try {
            $success = $this->offerModel->updateOffer($offerId, $offerData, $competences);

            if ($success) {
                $this->addFlashMessage('success', 'L\'offre #' . $offerId . ' a été mise à jour avec succès');
                header('Location: /pilotes/offres/' . $offerId);
            } else {
                throw new \Exception("Échec de mise à jour sans exception");
            }
        } catch (\Exception $e) {
            // Journalisation technique de l'erreur interne
            error_log('Erreur technique lors de la mise à jour de l\'offre #' . $offerId . ': ' . $e->getMessage());

            // Message utilisateur sans divulgation de détails techniques
            $this->addFlashMessage('error', 'Une erreur est survenue lors de la mise à jour de l\'offre. L\'équipe technique a été notifiée (Ref #' . time() . ')');
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

        $this->render('pilotes/offres/delete.html.twig', [
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
        $success = $this->offerModel->delete($offerId);

        if ($success) {
            $this->addFlashMessage('success', 'Offre supprimée avec succès');
        } else {
            $this->addFlashMessage('error', 'Erreur lors de la suppression de l\'offre');
        }

        header('Location: /pilotes/offres');
    }
}