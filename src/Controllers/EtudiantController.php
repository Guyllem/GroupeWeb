<?php
namespace App\Controllers;

use App\Models\StudentModel;
use App\Models\OfferModel;

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

        $userId = $_SESSION['user_id'];
        $studentId = $this->studentModel->getStudentIdFromUserId($userId);

        // Récupérer les offres récentes avec pagination
        $page = $_GET['page'] ?? 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $offers = $this->offerModel->getRecentOffers($limit, $offset);

        // Pour chaque offre, vérifier si elle est dans la wishlist de l'étudiant
        foreach ($offers as &$offer) {
            $offer['is_wishlisted'] = $this->offerModel->isInWishlist($offer['Id_Offre'], $studentId);
        }

        $this->render('etudiant/index.html.twig', [
            'offers' => $offers,
            'current_page' => $page
        ]);
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

        // Debug pour vérifier l'ID reçu
        error_log("ID de l'offre: " . $offerId);

        // Récupération de l'offre avec vérification du résultat
        $offer = $this->offerModel->getOfferDetails($offerId);

        // Debug pour vérifier la structure
        error_log("Structure de l'offre: " . print_r($offer, true));

        // Vérification que l'offre existe et a la structure attendue
        if (!$offer || !isset($offer['Titre_Offre'])) {
            $this->addFlashMessage('error', 'Offre non trouvée ou format invalide');
            header('Location: /etudiant');
            return;
        }

        // Générer le token CSRF
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        echo $this->twig->render('offres/postuler.html.twig', [
            'offer' => $offer,
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    }

    /**
     * Postuler à une offre
     */
    public function validate_application($params = null) {
        $this->requireEtudiant();

        // Récupérer l'ID de l'offre depuis les paramètres ou le POST
        $offerId = null;
        if ($params && isset($params['id'])) {
            $offerId = $params['id'];
        } else if (isset($_POST['offer_id'])) {
            $offerId = $_POST['offer_id'];
        }

        if (!$offerId) {
            $this->addFlashMessage('error', 'ID offre manquant');
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            return;
        }

        $userId = $_SESSION['user_id'];
        $studentId = $this->studentModel->getStudentIdFromUserId($userId);

        // Vérifier si l'étudiant a déjà postulé à cette offre
        $applications = $this->studentModel->getStudentApplications($studentId);
        $alreadyApplied = false;

        foreach ($applications as $application) {
            if ($application['Id_Offre'] == $offerId) {
                $alreadyApplied = true;
                break;
            }
        }

        if ($alreadyApplied) {
            $this->addFlashMessage('warning', 'Vous avez déjà postulé à cette offre');
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            return;
        }

        // Traitement des pièces jointes (CV, LM, etc.)
        $fileIds = [];
        if (isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
            // Traitement du CV
            // Code pour sauvegarder le fichier et créer une entrée dans la table Fichier
            // ...
            // $fileIds[] = $fileId;
        }

        if (isset($_FILES['lm']) && $_FILES['lm']['error'] === UPLOAD_ERR_OK) {
            // Traitement de la lettre de motivation
            // Code pour sauvegarder le fichier et créer une entrée dans la table Fichier
            // ...
            // $fileIds[] = $fileId;
        }

        // Créer la candidature
        $candidatureId = $this->studentModel->applyToOffer($studentId, $offerId);

        if ($candidatureId) {
            // Associer les fichiers à la candidature si nécessaire
            // Code pour insérer des entrées dans la table Contenir
            // ...

            $this->addFlashMessage('success', 'Votre candidature a bien été enregistrée');
        } else {
            $this->addFlashMessage('error', 'Une erreur est survenue lors de l\'enregistrement de votre candidature');
        }

        // Rediriger vers la page précédente ou la page des offres
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/offres'));
    }

    /**
     * Méthode pour appliquer à une offre depuis l'URL /offres/details/:id/postuler
     */
    public function apply($params) {
        return $this->validate_application($params);
    }
}