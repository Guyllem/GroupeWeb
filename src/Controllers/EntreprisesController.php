<?php
namespace App\Controllers;

use App\Models\EnterpriseModel;
use PDO;

class EntreprisesController extends BaseController {
    private $enterpriseModel;

    public function __construct($twig, $db) {
        parent::__construct($twig, $db);
        $this->enterpriseModel = new EnterpriseModel($db);
    }

    /**
     * Affiche la liste des entreprises
     */
    public function index() {
        $this->requireAuth();

        $userId = $_SESSION['user_id'] ?? null;

        // Obtenir les entreprises populaires
        $enterprises = $this->enterpriseModel->getTopRatedEnterprises();

        // Pour chaque entreprise, vérifier si elle est aimée par l'utilisateur actuel
        foreach ($enterprises as &$enterprise) {
            $enterprise['is_liked'] = $userId ? $this->enterpriseModel->isLikedByUser($enterprise['Id_Entreprise'], $userId) : false;
        }

        echo $this->twig->render('entreprises/index.html.twig', [
            'enterprises' => $enterprises
        ]);
    }

    /**
     * Affiche les détails d'une entreprise
     */
    public function details($params) {
        $this->requireAuth();

        $enterpriseId = $params['id'] ?? null;

        if (!$enterpriseId) {
            echo $this->twig->render('error.html.twig', [
                'message' => 'Entreprise non trouvée'
            ]);
            return;
        }

        $userId = $_SESSION['user_id'] ?? null;

        // Obtenir les détails de l'entreprise
        $enterprise = $this->enterpriseModel->getEnterpriseDetails($enterpriseId);

        if (!$enterprise) {
            echo $this->twig->render('error.html.twig', [
                'message' => 'Entreprise non trouvée'
            ]);
            return;
        }

        // Vérifier si elle est aimée par l'utilisateur actuel
        $enterprise['is_liked'] = $userId ? $this->enterpriseModel->isLikedByUser($enterpriseId, $userId) : false;

        echo $this->twig->render('entreprises/details.html.twig', [
            'enterprise' => $enterprise
        ]);
    }

    /**
     * Recherche des entreprises selon des critères
     */
    public function rechercher() {
        $this->requireAuth();

        // Obtenir les critères de recherche depuis le formulaire
        $search = $_POST['search'] ?? '';
        $sector = $_POST['sector'] ?? '';
        $location = $_POST['location'] ?? '';

        $conn = $this->db->connect();
        $userId = $_SESSION['user_id'] ?? null;

        // Construire la requête de recherche
        $query = '
            SELECT e.Id_Entreprise, e.Nom_Entreprise, e.Description_Entreprise,
                   AVG(ev.Note_Evaluer) as rating
            FROM Entreprise e
            LEFT JOIN Evaluer ev ON e.Id_Entreprise = ev.Id_Entreprise
            LEFT JOIN Fournir f ON e.Id_Entreprise = f.Id_Entreprise
            LEFT JOIN Secteur s ON f.Id_Secteur = s.Id_Secteur
            LEFT JOIN Localisation l ON e.Id_Localisation = l.Id_Localisation
            WHERE 1=1
        ';

        $params = [];

        if (!empty($search)) {
            $query .= ' AND (e.Nom_Entreprise LIKE :search OR e.Description_Entreprise LIKE :search)';
            $params[':search'] = '%' . $search . '%';
        }

        if (!empty($sector)) {
            $query .= ' AND s.Nom_Secteur LIKE :sector';
            $params[':sector'] = '%' . $sector . '%';
        }

        if (!empty($location)) {
            $query .= ' AND (l.Ville_Localisation LIKE :location OR l.Code_Postal_Localisation LIKE :location)';
            $params[':location'] = '%' . $location . '%';
        }

        $query .= ' GROUP BY e.Id_Entreprise ORDER BY rating DESC';

        $stmt = $conn->prepare($query);

        foreach ($params as $param => $value) {
            $stmt->bindValue($param, $value);
        }

        $stmt->execute();
        $enterprises = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Pour chaque entreprise, obtenir ses secteurs et vérifier si elle est aimée
        foreach ($enterprises as &$enterprise) {
            // Obtenir les secteurs (comme tags)
            $stmt = $conn->prepare('
                SELECT s.Nom_Secteur
                FROM Secteur s
                JOIN Fournir f ON s.Id_Secteur = f.Id_Secteur
                WHERE f.Id_Entreprise = :enterpriseId
            ');
            $stmt->bindParam(':enterpriseId', $enterprise['Id_Entreprise']);
            $stmt->execute();
            $tags = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $enterprise['tags'] = $tags;

            // Vérifier si elle est aimée par l'utilisateur actuel
            $enterprise['is_liked'] = false;
            if ($userId) {
                $stmt = $conn->prepare('
                    SELECT COUNT(*) FROM Evaluer
                    WHERE Id_Entreprise = :enterpriseId AND Id_Utilisateur = :userId
                ');
                $stmt->bindParam(':enterpriseId', $enterprise['Id_Entreprise']);
                $stmt->bindParam(':userId', $userId);
                $stmt->execute();
                $enterprise['is_liked'] = $stmt->fetchColumn() > 0;
            }
        }

        echo $this->twig->render('entreprises/index.html.twig', [
            'enterprises' => $enterprises,
            'search_criteria' => [
                'search' => $search,
                'sector' => $sector,
                'location' => $location
            ]
        ]);
    }

    /*
     * Affiche le formulaire d'évaluation d'une entreprise
     *
     * @param array $params Paramètres de la route contenant l'ID de l'entreprise
     * @return void
     */
    public function afficherRate($params)
    {
        $this->requireAuth();

        $enterpriseId = $params['id'] ?? null;

        if (!$enterpriseId) {
            echo $this->twig->render('error.html.twig', [
                'message' => 'Entreprise non trouvée'
            ]);
            return;
        }

        // Récupérer les détails de l'entreprise
        $enterprise = $this->enterpriseModel->getEnterpriseDetails($enterpriseId);

        if (!$enterprise) {
            echo $this->twig->render('error.html.twig', [
                'message' => 'Entreprise non trouvée'
            ]);
            return;
        }

        // Générer le token CSRF pour le formulaire
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        // Afficher le template d'évaluation
        echo $this->twig->render('entreprises/evaluer.html.twig', [
            'enterprise' => $enterprise,
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    }



    /**
     * Évalue une entreprise
     */
    public function rate($params) {
        $this->requireAuth();

        $enterpriseId = $params['id'] ?? null;
        $userId = $_SESSION['user_id'] ?? null;
        $rating = $_POST['rating'] ?? null;

        if (!$enterpriseId || !$userId || $rating === null || $rating < 1 || $rating > 5) {
            $this->addFlashMessage('error', 'Données d\'évaluation invalides');
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            return;
        }

        // Évaluer l'entreprise
        $result = $this->enterpriseModel->rateEnterprise($enterpriseId, $userId, $rating);

        if ($result) {
            $this->addFlashMessage('success', 'Votre évaluation a bien été enregistrée');
        } else {
            $this->addFlashMessage('error', 'Une erreur est survenue lors de l\'enregistrement de votre évaluation');
        }

        // Rediriger vers la page des détails de l'entreprise
        header('Location: /entreprises/details/' . $enterpriseId);
    }
}