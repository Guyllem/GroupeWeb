<?php
namespace App\Controllers;

use PDO;

class EntreprisesController extends BaseController {
    public function __construct($twig, $db) {
        parent::__construct($twig, $db);
    }

    public function index() {
        $this->requireAuth();
        
        $conn = $this->db->connect();
        $userId = $_COOKIE['user_id'] ?? null;
        
        // Obtenir les 10 premières entreprises classées par note
        $stmt = $conn->prepare('
            SELECT e.Id_Entreprise, e.Nom_Entreprise, e.Description_Entreprise,
                   AVG(ev.Note_Evaluer) as rating
            FROM Entreprise e
            LEFT JOIN Evaluer ev ON e.Id_Entreprise = ev.Id_Entreprise
            GROUP BY e.Id_Entreprise
            ORDER BY rating DESC
            LIMIT 10
        ');
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
        
        $this->render('entreprises/index.html.twig', [
            'enterprises' => $enterprises
        ]);
    }

    public function details($params) {
        $this->requireAuth();
        
        $enterpriseId = $params['id'] ?? null;
        
        if (!$enterpriseId) {
            $this->render('error.html.twig', [
                'message' => 'Entreprise non trouvée'
            ]);
            return;
        }
        
        $conn = $this->db->connect();
        $userId = $_COOKIE['user_id'] ?? null;
        
        // Obtenir les détails de l'entreprise
        $stmt = $conn->prepare('
            SELECT e.Id_Entreprise, e.Nom_Entreprise, e.Description_Entreprise,
                   e.Email_Entreprise, e.Telephone_Entreprise, e.Effectif_Entreprise,
                   l.Ville_Localisation, l.Code_Postal_Localisation, l.Adresse_Localisation
            FROM Entreprise e
            JOIN Localisation l ON e.Id_Localisation = l.Id_Localisation
            WHERE e.Id_Entreprise = :enterpriseId
        ');
        $stmt->bindParam(':enterpriseId', $enterpriseId);
        $stmt->execute();
        $enterprise = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$enterprise) {
            $this->render('error.html.twig', [
                'message' => 'Entreprise non trouvée'
            ]);
            return;
        }
        
        // Obtenir les secteurs (comme tags)
        $stmt = $conn->prepare('
            SELECT s.Nom_Secteur
            FROM Secteur s
            JOIN Fournir f ON s.Id_Secteur = f.Id_Secteur
            WHERE f.Id_Entreprise = :enterpriseId
        ');
        $stmt->bindParam(':enterpriseId', $enterpriseId);
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
            $stmt->bindParam(':enterpriseId', $enterpriseId);
            $stmt->bindParam(':userId', $userId);
            $stmt->execute();
            $enterprise['is_liked'] = $stmt->fetchColumn() > 0;
        }
        
        // Obtenir la note moyenne
        $stmt = $conn->prepare('
            SELECT AVG(Note_Evaluer) as rating
            FROM Evaluer
            WHERE Id_Entreprise = :enterpriseId
        ');
        $stmt->bindParam(':enterpriseId', $enterpriseId);
        $stmt->execute();
        $rating = $stmt->fetchColumn();
        $enterprise['rating'] = $rating ?: 0;
        
        $this->render('entreprises/details.html.twig', [
            'enterprise' => $enterprise
        ]);
    }

    /**
     * Affiche le formulaire d'évaluation d'une entreprise
     *
     * @param array $params Les paramètres de la route (id de l'entreprise)
     * @return void
     */
    public function afficherRate($params) {
        // Vérifier que l'utilisateur est authentifié
        $this->requireAuth();

        $enterpriseId = $params['id'] ?? null;

        if (!$enterpriseId) {
            $this->addFlashMessage('error', 'Entreprise non trouvée');
            header('Location: /entreprises');
            exit;
        }

        $conn = $this->db->connect();

        // Récupérer les détails de l'entreprise
        $stmt = $conn->prepare('
        SELECT e.Id_Entreprise, e.Nom_Entreprise, e.Description_Entreprise
        FROM Entreprise e
        WHERE e.Id_Entreprise = :enterpriseId
    ');
        $stmt->bindParam(':enterpriseId', $enterpriseId);
        $stmt->execute();
        $enterprise = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$enterprise) {
            $this->addFlashMessage('error', 'Entreprise non trouvée');
            header('Location: /entreprises');
            exit;
        }

        // Récupérer les secteurs (tags) de l'entreprise
        $stmt = $conn->prepare('
        SELECT s.Id_Secteur, s.Nom_Secteur
        FROM Secteur s
        JOIN Fournir f ON s.Id_Secteur = f.Id_Secteur
        WHERE f.Id_Entreprise = :enterpriseId
    ');
        $stmt->bindParam(':enterpriseId', $enterpriseId);
        $stmt->execute();
        $tags = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $enterprise['tags'] = $tags;

        // Générer un token CSRF pour protéger le formulaire
        $csrfToken = \App\Utils\SecurityUtil::generateCsrfToken();

        // Afficher le template avec les données
        $this->render('entreprises/evaluer.html.twig', [
            'enterprise' => $enterprise,
            'csrf_token' => $csrfToken
        ]);
    }

    /**
     * Traite l'évaluation d'une entreprise
     *
     * @param array $params Les paramètres de la route (id de l'entreprise)
     * @return void
     */
    public function rate($params) {
        // Vérifier que l'utilisateur est authentifié
        $this->requireAuth();

        $enterpriseId = $params['id'] ?? null;

        if (!$enterpriseId) {
            $this->addFlashMessage('error', 'Entreprise non trouvée');
            header('Location: /entreprises');
            exit;
        }

        // Vérifier que la note a été soumise
        if (!isset($_POST['rating']) || !is_numeric($_POST['rating'])) {
            $this->addFlashMessage('error', 'Veuillez sélectionner une note valide');
            header('Location: /entreprises/details/' . $enterpriseId . '/evaluer');
            exit;
        }

        $rating = (int) $_POST['rating'];

        // Valider la plage de la note (1-5)
        if ($rating < 1 || $rating > 5) {
            $this->addFlashMessage('error', 'La note doit être comprise entre 1 et 5');
            header('Location: /entreprises/details/' . $enterpriseId . '/evaluer');
            exit;
        }

        // Récupérer l'ID de l'utilisateur courant depuis la session
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            $this->addFlashMessage('error', 'Vous devez être connecté pour évaluer une entreprise');
            header('Location: /login');
            exit;
        }

        // Créer une instance du modèle Enterprise
        $enterpriseModel = new \App\Models\EnterpriseModel($this->db);

        // Enregistrer l'évaluation dans la base de données
        $success = $enterpriseModel->rateEnterprise($enterpriseId, $userId, $rating);

        if ($success) {
            $this->addFlashMessage('success', 'Votre évaluation a été enregistrée avec succès');
            header('Location: /entreprises/details/' . $enterpriseId);
            exit;
        } else {
            $this->addFlashMessage('error', 'Une erreur est survenue lors de l\'enregistrement de votre évaluation');
            header('Location: /entreprises/details/' . $enterpriseId . '/evaluer');
            exit;
        }
    }
}
