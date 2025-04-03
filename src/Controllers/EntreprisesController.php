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
}
