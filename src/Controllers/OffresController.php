<?php
namespace App\Controllers;

use App\Models\OfferModel;
use App\Models\StudentModel;
use PDO;

class OffresController extends BaseController {
    private $offerModel;
    private $studentModel;

    public function __construct($twig, $db) {
        parent::__construct($twig, $db);
        $this->offerModel = new OfferModel($db);
        $this->studentModel = new StudentModel($db);
    }

    /**
     * Affiche la liste des offres
     */
    public function index() {
        $this->requireAuth();

        $userId = $_SESSION['user_id'] ?? null;
        $studentId = null;

        // Obtenir l'ID de l'étudiant si l'utilisateur est un étudiant
        if ($userId) {
            $studentId = $this->studentModel->getStudentIdFromUserId($userId);
        }

        // Obtenir les offres récentes avec pagination
        $page = $_GET['page'] ?? 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $offers = $this->offerModel->getRecentOffers($limit, $offset);

        // Pour chaque offre, vérifier si elle est dans la wishlist de l'étudiant
        foreach ($offers as &$offer) {
            $offer['is_wishlisted'] = $studentId ? $this->offerModel->isInWishlist($offer['Id_Offre'], $studentId) : false;
        }
        
        echo $this->twig->render('etudiant/index.html.twig', [
            'offers' => $offers,
            'current_page' => $page
        ]);
    }

    /**
     * Affiche les détails d'une offre
     */
    public function details($params) {
        $this->requireAuth();

        $offerId = $params['id'] ?? null;

        if (!$offerId) {
            echo $this->twig->render('error.html.twig', [
                'message' => 'Offre non trouvée'
            ]);
            return;
        }

        $userId = $_SESSION['user_id'] ?? null;
        $studentId = null;

        // Obtenir l'ID de l'étudiant si l'utilisateur est un étudiant
        if ($userId) {
            $studentId = $this->studentModel->getStudentIdFromUserId($userId);
        }

        // Obtenir les détails de l'offre
        $offer = $this->offerModel->getOfferDetails($offerId);

        if (!$offer) {
            echo $this->twig->render('error.html.twig', [
                'message' => 'Offre non trouvée'
            ]);
            return;
        }

        // Vérifier si l'offre est dans la wishlist de l'étudiant
        $offer['is_wishlisted'] = $studentId ? $this->offerModel->isInWishlist($offerId, $studentId) : false;

        echo $this->twig->render('offres/details.html.twig', [
            'offer' => $offer
        ]);
    }

    /**
     * Recherche des offres selon des critères
     */
    public function rechercher() {
        $this->requireAuth();

        $criteria = [];

        // Récupérer les critères de recherche
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!empty($_POST['titre'])) {
                $criteria['titre'] = $_POST['titre'];
            }

            if (!empty($_POST['competence'])) {
                $criteria['competence'] = $_POST['competence'];
            }

            if (!empty($_POST['ville'])) {
                $criteria['ville'] = $_POST['ville'];
            }

            if (!empty($_POST['entreprise'])) {
                $criteria['entreprise'] = $_POST['entreprise'];
            }

            if (!empty($_POST['minRemuneration'])) {
                $criteria['minRemuneration'] = (int)$_POST['minRemuneration'];
            }

            if (!empty($_POST['minDuree'])) {
                $criteria['minDuree'] = (int)$_POST['minDuree'];
            }

            if (!empty($_POST['maxDuree'])) {
                $criteria['maxDuree'] = (int)$_POST['maxDuree'];
            }

            if (!empty($_POST['orderBy'])) {
                $criteria['orderBy'] = $_POST['orderBy'];
            }
        }

        // Valeurs par défaut pour la pagination
        $page = $_POST['page'] ?? 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $criteria['limit'] = $limit;
        $criteria['offset'] = $offset;

        // Effectuer la recherche
        $offers = $this->offerModel->searchOffers($criteria);

        // Vérifier si les offres sont dans la wishlist de l'étudiant
        $userId = $_SESSION['user_id'] ?? null;
        $studentId = null;

        if ($userId) {
            $studentId = $this->studentModel->getStudentIdFromUserId($userId);

            foreach ($offers as &$offer) {
                $offer['is_wishlisted'] = $this->offerModel->isInWishlist($offer['Id_Offre'], $studentId);
            }
        }

        echo $this->twig->render('etudiant/index.html.twig', [
            'offers' => $offers,
            'current_page' => $page,
            'criteria' => $criteria
        ]);
    }

    /**
     * Ajoute une offre à la wishlist
     */
    public function add_to_wishlist($params) {
        $this->requireAuth();

        $offerId = $params['id'] ?? null;

        if (!$offerId) {
            $this->addFlashMessage('error', 'Offre non trouvée');
            header('Location: /offres');
            return;
        }

        $userId = $_SESSION['user_id'] ?? null;
        $studentId = $this->studentModel->getStudentIdFromUserId($userId);

        if (!$studentId) {
            $this->addFlashMessage('error', 'Vous devez être connecté en tant qu\'étudiant');
            header('Location: /offres');
            return;
        }

        $result = $this->studentModel->addToWishlist($studentId, $offerId);

        if ($result) {
            $this->addFlashMessage('success', 'Offre ajoutée à votre wishlist');
        } else {
            $this->addFlashMessage('error', 'Erreur lors de l\'ajout à la wishlist');
        }

        // Rediriger vers la page précédente ou la page des offres
        $referer = $_SERVER['HTTP_REFERER'] ?? '/offres';
        header('Location: ' . $referer);
    }

    /**
     * Retire une offre de la wishlist
     */
    public function remove_from_wishlist($params) {
        $this->requireAuth();

        $offerId = $params['id'] ?? null;

        if (!$offerId) {
            $this->addFlashMessage('error', 'Offre non trouvée');
            header('Location: /offres');
            return;
        }

        $userId = $_SESSION['user_id'] ?? null;
        $studentId = $this->studentModel->getStudentIdFromUserId($userId);

        if (!$studentId) {
            $this->addFlashMessage('error', 'Vous devez être connecté en tant qu\'étudiant');
            header('Location: /offres');
            return;
        }

        $result = $this->studentModel->removeFromWishlist($studentId, $offerId);

        if ($result) {
            $this->addFlashMessage('success', 'Offre retirée de votre wishlist');
        } else {
            $this->addFlashMessage('error', 'Erreur lors du retrait de la wishlist');
        }

        // Rediriger vers la page précédente ou la page des offres
        $referer = $_SERVER['HTTP_REFERER'] ?? '/offres';
        header('Location: ' . $referer);
    }
}