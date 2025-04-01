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

    public function mesOffres() {
        $this->requireEtudiant();

        $userId = $_SESSION['user_id'];
        $studentId = $this->studentModel->getStudentIdFromUserId($userId);

        $offers = $this->studentModel->getStudentApplications($studentId);

        $this->render('etudiant/mes_offres.html.twig', [
            'offers' => $offers
        ]);
    }

    public function wishlist() {
        $this->requireEtudiant();

        $userId = $_SESSION['user_id'];
        $studentId = $this->studentModel->getStudentIdFromUserId($userId);

        $offers = $this->studentModel->getStudentWishlist($studentId);

        $this->render('etudiant/wishlist.html.twig', [
            'offers' => $offers
        ]);
    }

    // Action pour ajouter une offre à la wishlist (AJAX)
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

    // Action pour retirer une offre de la wishlist (AJAX)
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

    // Action pour postuler à une offre
    public function postuler() {
        $this->requireEtudiant();

        $offerId = $_POST['offer_id'] ?? null;
        $userId = $_SESSION['user_id'];
        $studentId = $this->studentModel->getStudentIdFromUserId($userId);

        if (!$offerId) {
            $this->addFlashMessage('error', 'ID offre manquant');
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            return;
        }

        $result = $this->studentModel->applyToOffer($studentId, $offerId);

        if ($result) {
            $this->addFlashMessage('success', 'Votre candidature a bien été enregistrée');
        } else {
            $this->addFlashMessage('error', 'Vous avez déjà postulé à cette offre ou une erreur est survenue');
        }

        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }
}