<?php
namespace App\Controllers;

use App\Models\OfferModel;
use App\Models\StudentModel;
use App\Models\EnterpriseModel;
use PDO;

class OffresController extends BaseController
{
    private $offerModel;
    private $studentModel;
    private $enterpriseModel;
    private $offresParPage = 10;

    /**
     * Constructeur avec injection des dépendances
     */
    public function __construct($twig, $db)
    {
        parent::__construct($twig, $db);
        $this->offerModel = new OfferModel($db);
        $this->studentModel = new StudentModel($db);
        $this->enterpriseModel = new EnterpriseModel($db);
    }

    /**
     * Affiche la liste paginée des offres
     */
    public function index($params = [])
    {
        $this->requireAuth();

        // Détermination de la page courante
        $currentPage = isset($params['page']) ? max(1, (int)$params['page']) : 1;
        $offset = ($currentPage - 1) * $this->offresParPage;

        // Récupération de l'ID étudiant si l'utilisateur est connecté
        $userId = $_SESSION['user_id'] ?? null;
        $studentId = null;

        if ($userId) {
            $studentId = $this->studentModel->getStudentIdFromUserId($userId);
        }

        // Comptage direct du nombre total d'offres pour la pagination
        $conn = $this->db->connect();
        $stmt = $conn->prepare('SELECT COUNT(*) FROM Offre');
        $stmt->execute();
        $totalOffres = $stmt->fetchColumn();
        $totalPages = ceil($totalOffres / $this->offresParPage);

        // Récupération des offres pour la page courante
        $offers = $this->offerModel->getRecentOffers($this->offresParPage, $offset);

        // Pour chaque offre, vérification si elle est dans la wishlist de l'étudiant
        if ($studentId) {
            foreach ($offers as &$offer) {
                $offer['is_wishlisted'] = $this->offerModel->isInWishlist($offer['Id_Offre'], $studentId);
            }
        }

        // Rendu de la vue
        $this->render('etudiant/index.html.twig', [
            'offers' => $offers,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages
        ]);
    }

    /**
     * Affiche les détails d'une offre spécifique
     */
    public function details($params)
    {
        $this->requireAuth();

        $offerId = $params['id'] ?? null;

        if (!$offerId) {
            $this->addFlashMessage('error', 'Offre non trouvée');
            header('Location: /offres');
            exit;
        }

        // Récupération des détails de l'offre via le modèle
        $offer = $this->offerModel->getOfferDetails($offerId);

        if (!$offer) {
            $this->addFlashMessage('error', 'Offre non trouvée');
            header('Location: /offres');
            exit;
        }

        // Récupération de l'ID étudiant si l'utilisateur est connecté
        $userId = $_SESSION['user_id'] ?? null;
        $studentId = null;

        if ($userId) {
            $studentId = $this->studentModel->getStudentIdFromUserId($userId);

            if ($studentId) {
                $offer['is_wishlisted'] = $this->offerModel->isInWishlist($offerId, $studentId);
            }
        }

        // Rendu de la vue
        $this->render('offres/details.html.twig', [
            'offer' => $offer
        ]);
    }

    /**
     * Recherche d'offres selon divers critères
     */
    public function rechercher()
    {
        $this->requireAuth();

        // Récupération des paramètres de recherche
        $criteria = [
            'titre' => $_POST['titre'] ?? null,
            'competence' => $_POST['competence'] ?? null,
            'ville' => $_POST['ville'] ?? null,
            'entreprise' => $_POST['entreprise'] ?? null,
            'minRemuneration' => $_POST['min_remuneration'] ?? null,
            'minDuree' => $_POST['min_duree'] ?? null,
            'maxDuree' => $_POST['max_duree'] ?? null,
            'orderBy' => $_POST['order_by'] ?? 'recent',
            'limit' => $this->offresParPage,
            'offset' => 0
        ];

        // Filtrage des critères vides
        $criteria = array_filter($criteria, function($value) {
            return $value !== null && $value !== '';
        });

        // Récupération des offres correspondant aux critères
        $offers = $this->offerModel->searchOffers($criteria);

        // Récupération de l'ID étudiant pour vérifier les wishlist
        $userId = $_SESSION['user_id'] ?? null;
        $studentId = null;

        if ($userId) {
            $studentId = $this->studentModel->getStudentIdFromUserId($userId);

            if ($studentId) {
                foreach ($offers as &$offer) {
                    $offer['is_wishlisted'] = $this->offerModel->isInWishlist($offer['Id_Offre'], $studentId);
                }
            }
        }

        // Rendu de la vue
        $this->render('etudiant/index.html.twig', [
            'offers' => $offers,
            'currentPage' => 1,
            'totalPages' => 1,
            'search_criteria' => $criteria
        ]);
    }

    /**
     * Ajoute une offre à la wishlist de l'étudiant connecté
     */
    public function add_to_wishlist($params)
    {
        $this->requireAuth();

        $offerId = $params['id'] ?? null;
        $userId = $_SESSION['user_id'] ?? null;

        if (!$offerId || !$userId) {
            $this->addFlashMessage('error', 'Paramètres invalides');
            $this->redirectToReferer();
            return;
        }

        $studentId = $this->studentModel->getStudentIdFromUserId($userId);
        if (!$studentId) {
            $this->addFlashMessage('error', 'Profil étudiant non trouvé');
            $this->redirectToReferer();
            return;
        }

        // Vérification de l'existence de l'offre
        $offer = $this->offerModel->getById($offerId);
        if (!$offer) {
            $this->addFlashMessage('error', 'Offre non trouvée');
            $this->redirectToReferer();
            return;
        }

        $result = $this->studentModel->addToWishlist($studentId, $offerId);

        if ($result) {
            $this->addFlashMessage('success', 'Offre ajoutée à votre wishlist');
        } else {
            $this->addFlashMessage('error', 'Erreur lors de l\'ajout à la wishlist');
        }

        $this->redirectToReferer();
    }

    /**
     * Retire une offre de la wishlist de l'étudiant connecté
     */
    public function remove_from_wishlist($params)
    {
        $this->requireAuth();

        $offerId = $params['id'] ?? null;
        $userId = $_SESSION['user_id'] ?? null;

        if (!$offerId || !$userId) {
            $this->addFlashMessage('error', 'Paramètres invalides');
            $this->redirectToReferer();
            return;
        }

        $studentId = $this->studentModel->getStudentIdFromUserId($userId);
        if (!$studentId) {
            $this->addFlashMessage('error', 'Profil étudiant non trouvé');
            $this->redirectToReferer();
            return;
        }

        $result = $this->studentModel->removeFromWishlist($studentId, $offerId);

        if ($result) {
            $this->addFlashMessage('success', 'Offre retirée de votre wishlist');
        } else {
            $this->addFlashMessage('error', 'Erreur lors du retrait de la wishlist');
        }

        // Gestion spéciale pour la page wishlist
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        if (strpos($referer, '/etudiant/wishlist') !== false) {
            header('Location: /etudiant/wishlist');
            exit;
        }

        $this->redirectToReferer();
    }

    /**
     * Méthode utilitaire pour rediriger vers la page précédente
     */
    private function redirectToReferer()
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/offres';
        header('Location: ' . $referer);
        exit;
    }
}