<?php
namespace App\Controllers;

use App\Models\StudentModel;
use App\Models\OfferModel;
use App\Models\FileModel;
use App\Utils\FileUploadUtil;

class EtudiantController extends BaseController {
    private $studentModel;
    private $offerModel;

    public function __construct($twig, $db) {
        parent::__construct($twig, $db);
        $this->studentModel = new StudentModel($db);
        $this->offerModel = new OfferModel($db);
    }

    /**
     * Affiche la page d'accueil de l'étudiant avec les offres récentes
     */
    public function index() {
        $this->requireEtudiant();

        header('Location: /offres');
    }

    /**
     * Affiche le profil de l'étudiant
     */
    public function profil() {
        $this->requireEtudiant();

        $userId = $_SESSION['user_id'];
        $studentId = $this->studentModel->getStudentIdFromUserId($userId);

        $student = $this->studentModel->getStudentInfo($studentId);
        $skills = $this->studentModel->getStudentSkills($studentId);

        $this->render('etudiant/mon_profil.html.twig', [
            'student' => $student,
            'skills' => $skills
        ]);
    }

    /**
     * Affiche les offres auxquelles l'étudiant a postulé
     */
    public function mesOffres() {
        $this->requireEtudiant();

        $userId = $_SESSION['user_id'];
        $studentId = $this->studentModel->getStudentIdFromUserId($userId);

        $offers = $this->studentModel->getStudentApplications($studentId);

        $this->render('etudiant/mes_offres.html.twig', [
            'offers' => $offers
        ]);
    }

    /**
     * Alias de mesOffres pour correspondre à l'URL demandée
     */
    public function my_applications() {
        return $this->mesOffres();
    }

    /**
     * Affiche la wishlist de l'étudiant
     */
    public function wishlist() {
        $this->requireEtudiant();

        $userId = $_SESSION['user_id'];
        $studentId = $this->studentModel->getStudentIdFromUserId($userId);

        $offers = $this->studentModel->getStudentWishlist($studentId);

        $this->render('etudiant/wishlist.html.twig', [
            'offers' => $offers
        ]);
    }

    /**
     * Ajoute une offre à la wishlist (AJAX)
     */
    public function ajouterWishlist() {
        $this->requireEtudiant();

        $offerId = $_POST['offer_id'] ?? null;
        $userId = $_SESSION['user_id'];
        $studentId = $this->studentModel->getStudentIdFromUserId($userId);

        if (!$offerId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID offre manquant']);
            return;
        }

        $result = $this->studentModel->addToWishlist($studentId, $offerId);

        echo json_encode(['success' => $result]);
    }

    /**
     * Retire une offre de la wishlist (AJAX)
     */
    public function retirerWishlist() {
        $this->requireEtudiant();

        $offerId = $_POST['offer_id'] ?? null;
        $userId = $_SESSION['user_id'];
        $studentId = $this->studentModel->getStudentIdFromUserId($userId);

        if (!$offerId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID offre manquant']);
            return;
        }

        $result = $this->studentModel->removeFromWishlist($studentId, $offerId);

        echo json_encode(['success' => $result]);
    }

    /**
     * Affiche la page de postulation
     *
     * @param array $params Paramètres de la route
     */
    public function postuler($params) {
        $this->requireEtudiant();

        // Vérification du format des paramètres reçus
        if (!is_array($params) || !isset($params['id'])) {
            $this->addFlashMessage('error', 'Paramètres invalides');
            header('Location: /etudiant');
            return;
        }

        $offerId = $params['id'];

        // Récupération de l'offre
        $offer = $this->offerModel->getOfferDetails($offerId);

        // Vérification que l'offre existe
        if (!$offer) {
            $this->addFlashMessage('error', 'Offre non trouvée');
            header('Location: /etudiant');
            return;
        }

        // Vérifier si l'étudiant a déjà postulé à cette offre
        $userId = $_SESSION['user_id'];
        $studentId = $this->studentModel->getStudentIdFromUserId($userId);
        $applications = $this->studentModel->getStudentApplications($studentId);

        foreach ($applications as $application) {
            if ($application['Id_Offre'] == $offerId) {
                $this->addFlashMessage('warning', 'Vous avez déjà postulé à cette offre');
                header('Location: /offres/details/' . $offerId);
                return;
            }
        }

        // Générer le token CSRF
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $this->render('offres/postuler.html.twig', [
            'offer' => $offer,
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    }

    /**
     * Gère la candidature à une offre avec upload de fichiers
     *
     * @param array $params Paramètres de la route
     */
    public function validate_application($params) {
        $this->requireEtudiant();

        // Vérification CSRF
        if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) ||
            $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $this->addFlashMessage('error', 'Erreur de sécurité. Veuillez réessayer.');
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            return;
        }

        // Récupérer l'ID de l'offre
        $offerId = $params['id'] ?? null;
        if (!$offerId) {
            $this->addFlashMessage('error', 'Offre non trouvée');
            header('Location: /etudiant');
            return;
        }

        // Vérifier que l'offre existe
        $offer = $this->offerModel->getOfferDetails($offerId);
        if (!$offer) {
            $this->addFlashMessage('error', 'Offre non trouvée');
            header('Location: /etudiant');
            return;
        }

        // Récupérer l'ID de l'étudiant
        $userId = $_SESSION['user_id'];
        $studentId = $this->studentModel->getStudentIdFromUserId($userId);
        if (!$studentId) {
            $this->addFlashMessage('error', 'Profil étudiant non trouvé');
            header('Location: /etudiant');
            return;
        }

        // Vérifier si l'étudiant a déjà postulé à cette offre
        $applications = $this->studentModel->getStudentApplications($studentId);
        foreach ($applications as $application) {
            if ($application['Id_Offre'] == $offerId) {
                $this->addFlashMessage('warning', 'Vous avez déjà postulé à cette offre');
                header('Location: /offres/details/' . $offerId);
                return;
            }
        }

        // Instancier les utilitaires et modèles nécessaires
        $fileUploadUtil = new FileUploadUtil();
        $fileModel = new FileModel($this->db);

        // Vérifier la présence des fichiers requis
        if (!isset($_FILES['cv']) || $_FILES['cv']['error'] === UPLOAD_ERR_NO_FILE ||
            !isset($_FILES['motivation']) || $_FILES['motivation']['error'] === UPLOAD_ERR_NO_FILE) {

            if (!isset($_FILES['cv']) || $_FILES['cv']['error'] === UPLOAD_ERR_NO_FILE) {
                $this->addFlashMessage('error', 'Curriculum Vitae (CV): Ce document est obligatoire.');
            }

            if (!isset($_FILES['motivation']) || $_FILES['motivation']['error'] === UPLOAD_ERR_NO_FILE) {
                $this->addFlashMessage('error', 'Lettre de motivation: Ce document est obligatoire.');
            }

            header('Location: /offres/details/' . $offerId . '/postuler');
            return;
        }

        // Traiter les uploads
        $cvFile = $fileUploadUtil->upload($_FILES['cv'], 'CV');
        $motivationFile = $fileUploadUtil->upload($_FILES['motivation'], 'LM');

        // Vérifier s'il y a des erreurs
        if (!$cvFile || !$motivationFile) {
            // Nettoyer les fichiers uploadés en cas d'erreur
            $fileUploadUtil->cleanUploadedFiles();

            foreach ($fileUploadUtil->getErrors() as $field => $error) {
                $this->addFlashMessage('error', $error);
            }
            header('Location: /offres/details/' . $offerId . '/postuler');
            return;
        }

        try {
            // Démarrer une transaction
            $conn = $this->db->connect();
            $conn->beginTransaction();

            // 1. Créer la candidature
            $candidatureId = $this->studentModel->applyToOffer($studentId, $offerId);
            if (!$candidatureId) {
                throw new \Exception("Erreur lors de la création de la candidature");
            }

            // 2. Enregistrer les fichiers en base de données
            $cvFileId = $fileModel->createFile('CV', $cvFile['original_name'], $cvFile['path']);
            if (!$cvFileId) {
                throw new \Exception("Erreur lors de l'enregistrement du CV");
            }

            $motivationFileId = $fileModel->createFile('LM', $motivationFile['original_name'], $motivationFile['path']);
            if (!$motivationFileId) {
                throw new \Exception("Erreur lors de l'enregistrement de la lettre de motivation");
            }

            // 3. Créer les relations entre la candidature et les fichiers
            $cvRelationSuccess = $this->studentModel->attachFileToCandidature($candidatureId, $cvFileId);
            $lmRelationSuccess = $this->studentModel->attachFileToCandidature($candidatureId, $motivationFileId);

            if (!$cvRelationSuccess || !$lmRelationSuccess) {
                throw new \Exception("Erreur lors de l'association des fichiers à la candidature");
            }

            // Valider la transaction
            $conn->commit();

            // Afficher un message de succès
            $this->addFlashMessage('success', 'Votre candidature a été enregistrée avec succès');

            // Rediriger vers la page de mes offres
            header('Location: /etudiant/mes_offres');

        } catch (\Exception $e) {
            // En cas d'erreur, annuler la transaction
            if (isset($conn) && $conn->inTransaction()) {
                $conn->rollBack();
            }

            // Journaliser l'erreur
            error_log("Erreur lors de la candidature: " . $e->getMessage());

            // Nettoyer les fichiers uploadés
            $fileUploadUtil->cleanUploadedFiles();

            // Afficher un message d'erreur
            $this->addFlashMessage('error', 'Une erreur est survenue lors de l\'enregistrement de votre candidature: ' . $e->getMessage());

            // Rediriger vers la page de postulation
            header('Location: /offres/details/' . $offerId . '/postuler');
        }
    }

    /**
     * Méthode pour appliquer à une offre depuis l'URL /offres/details/:id/postuler
     */
    public function apply($params) {
        $this->validate_application($params);
    }
}