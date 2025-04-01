<?php
namespace App\Controllers;

use PDO;

class OffresController extends BaseController {
    private $offresParPage = 5;

    public function __construct($twig, $db) {
        parent::__construct($twig, $db);
    }

    public function index($params = []) {
        $this->requireAuth();

        // Récupérer le numéro de page depuis les paramètres
        $currentPage = isset($params['page']) ? max(1, (int)$params['page']) : 1;
        $offset = ($currentPage - 1) * $this->offresParPage;

        $conn = $this->db->connect();
        $userId = $_COOKIE['user_id'] ?? null;
        $studentId = null;

        // Obtenir l'ID de l'étudiant si l'utilisateur est un étudiant
        if ($userId) {
            $stmt = $conn->prepare('SELECT Id_Etudiant FROM Etudiant WHERE Id_Utilisateur = :userId');
            $stmt->bindParam(':userId', $userId);
            $stmt->execute();
            $student = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($student) {
                $studentId = $student['Id_Etudiant'];
            }
        }

        // Compter le nombre total d'offres pour la pagination
        $stmt = $conn->prepare('SELECT COUNT(*) FROM Offre');
        $stmt->execute();
        $totalOffres = $stmt->fetchColumn();
        $totalPages = ceil($totalOffres / $this->offresParPage);

        // Obtenir les offres pour la page actuelle avec limite et offset
        $stmt = $conn->prepare('
            SELECT o.Id_Offre, o.Titre_Offre, o.Description_Offre, o.Date_Debut_Offre
            FROM Offre o
            ORDER BY o.Date_Debut_Offre DESC
            LIMIT :limit OFFSET :offset
        ');
        $stmt->bindParam(':limit', $this->offresParPage, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $offers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Pour chaque offre, obtenir ses compétences et vérifier si elle est dans la liste de souhaits
        foreach ($offers as &$offer) {
            // Obtenir les compétences
            $stmt = $conn->prepare('
                SELECT c.Nom_Competence
                FROM Competence c
                JOIN Necessiter n ON c.Id_Competence = n.Id_Competence
                WHERE n.Id_Offre = :offerId
            ');
            $stmt->bindParam(':offerId', $offer['Id_Offre']);
            $stmt->execute();
            $skills = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $offer['skills'] = $skills;

            // Vérifier si elle est dans la liste de souhaits de l'étudiant actuel
            $offer['is_wishlisted'] = false;
            if ($studentId) {
                $stmt = $conn->prepare('
                    SELECT COUNT(*) FROM Souhaiter
                    WHERE Id_Offre = :offerId AND Id_Etudiant = :studentId
                ');
                $stmt->bindParam(':offerId', $offer['Id_Offre']);
                $stmt->bindParam(':studentId', $studentId);
                $stmt->execute();
                $offer['is_wishlisted'] = $stmt->fetchColumn() > 0;
            }
        }

        echo $this->twig->render('etudiant/index.html.twig', [
            'offers' => $offers,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages
        ]);
    }

    public function details($params) {
        $this->requireAuth();

        $offerId = $params['id'] ?? null;

        if (!$offerId) {
            echo $this->twig->render('error.html.twig', [
                'message' => 'Offre non trouvée'
            ]);
            return;
        }

        $conn = $this->db->connect();
        $userId = $_COOKIE['user_id'] ?? null;
        $studentId = null;

        // Obtenir l'ID de l'étudiant si l'utilisateur est un étudiant
        if ($userId) {
            $stmt = $conn->prepare('SELECT Id_Etudiant FROM Etudiant WHERE Id_Utilisateur = :userId');
            $stmt->bindParam(':userId', $userId);
            $stmt->execute();
            $student = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($student) {
                $studentId = $student['Id_Etudiant'];
            }
        }

        // Obtenir les détails de l'offre
        $stmt = $conn->prepare('
            SELECT o.Id_Offre, o.Titre_Offre, o.Description_Offre, o.Remuneration_Offre,
                   o.Niveau_Requis_Offre, o.Date_Debut_Offre, o.Duree_Min_Offre, o.Duree_Max_Offre,
                   e.Nom_Entreprise, l.Ville_Localisation, l.Code_Postal_Localisation, l.Adresse_Localisation
            FROM Offre o
            JOIN Entreprise e ON o.Id_Entreprise = e.Id_Entreprise
            JOIN Localisation l ON e.Id_Localisation = l.Id_Localisation
            WHERE o.Id_Offre = :offerId
        ');
        $stmt->bindParam(':offerId', $offerId);
        $stmt->execute();
        $offer = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$offer) {
            echo $this->twig->render('error.html.twig', [
                'message' => 'Offre non trouvée'
            ]);
            return;
        }

        // Obtenir les compétences
        $stmt = $conn->prepare('
            SELECT c.Nom_Competence
            FROM Competence c
            JOIN Necessiter n ON c.Id_Competence = n.Id_Competence
            WHERE n.Id_Offre = :offerId
        ');
        $stmt->bindParam(':offerId', $offerId);
        $stmt->execute();
        $skills = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $offer['skills'] = $skills;

        // Vérifier si elle est dans la liste de souhaits de l'étudiant actuel
        $offer['is_wishlisted'] = false;
        if ($studentId) {
            $stmt = $conn->prepare('
                SELECT COUNT(*) FROM Souhaiter
                WHERE Id_Offre = :offerId AND Id_Etudiant = :studentId
            ');
            $stmt->bindParam(':offerId', $offerId);
            $stmt->bindParam(':studentId', $studentId);
            $stmt->execute();
            $offer['is_wishlisted'] = $stmt->fetchColumn() > 0;
        }

        echo $this->twig->render('offres/details.html.twig', [
            'offer' => $offer
        ]);
    }
}
