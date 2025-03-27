<?php
namespace App\Models;

use PDO;

class PilotModel extends Model {
    public function __construct($db) {
        parent::__construct($db, 'Pilote', 'Id_Pilote');
    }

    /**
     * Obtient tous les pilotes triés par nom
     *
     * @param int $limit Nombre maximum de pilotes à retourner
     * @param int $offset Position de départ
     * @return array Liste des pilotes
     */
    public function getPilotsByName($limit = 10, $offset = 0) {
        $query = '
            SELECT 
                p.Id_Pilote, 
                u.Id_Utilisateur,
                u.Nom_Utilisateur, 
                u.Prenom_Utilisateur,
                c.Nom_Campus,
                c.Id_Campus,
                COUNT(DISTINCT s.Id_Promotion) as promotion_count
            FROM Pilote p
            JOIN Utilisateur u ON p.Id_Utilisateur = u.Id_Utilisateur
            LEFT JOIN Superviser s ON p.Id_Pilote = s.Id_Pilote
            LEFT JOIN Promotion pr ON s.Id_Promotion = pr.Id_Promotion
            LEFT JOIN Campus c ON pr.Id_Campus = c.Id_Campus
            GROUP BY p.Id_Pilote, u.Nom_Utilisateur, u.Prenom_Utilisateur
            ORDER BY u.Nom_Utilisateur, u.Prenom_Utilisateur
            LIMIT :limit OFFSET :offset
        ';

        $conn = $this->db->connect();
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtient les détails d'un pilote
     *
     * @param int $pilotId ID du pilote
     * @return array|null Détails du pilote ou null si non trouvé
     */
    public function getPilotDetails($pilotId) {
        $query = '
            SELECT 
                p.Id_Pilote, 
                u.Id_Utilisateur,
                u.Nom_Utilisateur, 
                u.Prenom_Utilisateur, 
                u.Email_Utilisateur,
                c.Nom_Campus,
                c.Id_Campus
            FROM Pilote p
            JOIN Utilisateur u ON p.Id_Utilisateur = u.Id_Utilisateur
            LEFT JOIN Superviser s ON p.Id_Pilote = s.Id_Pilote
            LEFT JOIN Promotion pr ON s.Id_Promotion = pr.Id_Promotion
            LEFT JOIN Campus c ON pr.Id_Campus = c.Id_Campus
            WHERE p.Id_Pilote = :pilotId
            GROUP BY p.Id_Pilote
        ';

        $conn = $this->db->connect();
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':pilotId', $pilotId);
        $stmt->execute();

        $pilot = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$pilot) {
            return null;
        }

        // Obtenir les promotions supervisées
        $pilot['promotions'] = $this->getSupervisedPromotions($pilotId);

        return $pilot;
    }

    /**
     * Obtient les promotions supervisées par un pilote
     *
     * @param int $pilotId ID du pilote
     * @return array Liste des promotions
     */
    public function getSupervisedPromotions($pilotId) {
        $query = '
            SELECT 
                p.Id_Promotion, 
                p.Nom_Promotion, 
                p.Specialite_Promotion,
                p.Statut_Promotion,
                p.Niveau_Promotion,
                s.Date_Debut_Superviser,
                s.Date_Fin_Superviser,
                c.Nom_Campus,
                c.Id_Campus
            FROM Superviser s
            JOIN Promotion p ON s.Id_Promotion = p.Id_Promotion
            LEFT JOIN Campus c ON p.Id_Campus = c.Id_Campus
            WHERE s.Id_Pilote = :pilotId
        ';

        $conn = $this->db->connect();
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':pilotId', $pilotId);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtient les étudiants supervisés par un pilote
     *
     * @param int $pilotId ID du pilote
     * @return array Liste des étudiants
     */
    public function getSupervisedStudents($pilotId) {
        $query = '
            SELECT DISTINCT
                e.Id_Etudiant,
                u.Id_Utilisateur,
                u.Nom_Utilisateur,
                u.Prenom_Utilisateur,
                p.Id_Promotion,
                p.Nom_Promotion,
                p.Specialite_Promotion,
                c.Id_Campus,
                c.Nom_Campus,
                (SELECT COUNT(*) FROM Candidature WHERE Id_Etudiant = e.Id_Etudiant) as application_count,
                (SELECT COUNT(*) FROM Souhaiter WHERE Id_Etudiant = e.Id_Etudiant) as wishlist_count
            FROM Superviser s
            JOIN Promotion p ON s.Id_Promotion = p.Id_Promotion
            JOIN Appartenir a ON p.Id_Promotion = a.Id_Promotion
            JOIN Etudiant e ON a.Id_Etudiant = e.Id_Etudiant
            JOIN Utilisateur u ON e.Id_Utilisateur = u.Id_Utilisateur
            LEFT JOIN Campus c ON p.Id_Campus = c.Id_Campus
            WHERE s.Id_Pilote = :pilotId
            ORDER BY u.Nom_Utilisateur, u.Prenom_Utilisateur
        ';

        $conn = $this->db->connect();
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':pilotId', $pilotId);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Attribue une promotion à un pilote
     *
     * @param int $pilotId ID du pilote
     * @param int $promotionId ID de la promotion
     * @param string $startDate Date de début (format 'Y-m-d')
     * @param string $endDate Date de fin (format 'Y-m-d')
     * @return bool Succès de l'opération
     */
    public function assignPromotion($pilotId, $promotionId, $startDate, $endDate) {
        $conn = $this->db->connect();

        try {
            // Vérifier si le pilote est déjà assigné à cette promotion
            $stmt = $conn->prepare('
                SELECT COUNT(*) FROM Superviser
                WHERE Id_Pilote = :pilotId AND Id_Promotion = :promotionId
            ');
            $stmt->bindParam(':pilotId', $pilotId);
            $stmt->bindParam(':promotionId', $promotionId);
            $stmt->execute();

            if ($stmt->fetchColumn() > 0) {
                // Mettre à jour l'assignation existante
                $stmt = $conn->prepare('
                    UPDATE Superviser
                    SET Date_Debut_Superviser = :startDate, Date_Fin_Superviser = :endDate
                    WHERE Id_Pilote = :pilotId AND Id_Promotion = :promotionId
                ');
            } else {
                // Créer une nouvelle assignation
                $stmt = $conn->prepare('
                    INSERT INTO Superviser (
                        Id_Pilote, 
                        Id_Promotion, 
                        Date_Debut_Superviser, 
                        Date_Fin_Superviser
                    ) VALUES (
                        :pilotId, 
                        :promotionId, 
                        :startDate, 
                        :endDate
                    )
                ');
            }

            $stmt->bindParam(':pilotId', $pilotId);
            $stmt->bindParam(':promotionId', $promotionId);
            $stmt->bindParam(':startDate', $startDate);
            $stmt->bindParam(':endDate', $endDate);

            return $stmt->execute();
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Obtient l'ID du pilote à partir de l'ID utilisateur
     *
     * @param int $userId ID de l'utilisateur
     * @return int|null ID du pilote ou null si non trouvé
     */
    public function getPilotIdFromUserId($userId) {
        $conn = $this->db->connect();
        $stmt = $conn->prepare('SELECT Id_Pilote FROM Pilote WHERE Id_Utilisateur = :userId');
        $stmt->bindParam(':userId', $userId);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? $result['Id_Pilote'] : null;
    }

    /**
     * Crée un nouveau pilote
     *
     * @param int $userId ID de l'utilisateur
     * @return int|null ID du pilote créé ou null si erreur
     */
    public function createPilot($userId) {
        $conn = $this->db->connect();

        try {
            $stmt = $conn->prepare('INSERT INTO Pilote (Id_Utilisateur) VALUES (:userId)');
            $stmt->bindParam(':userId', $userId);
            $stmt->execute();

            return $conn->lastInsertId();
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return null;
        }
    }
}