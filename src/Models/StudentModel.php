<?php
namespace App\Models;

use PDO;

class StudentModel extends Model {
    public function __construct($db) {
        parent::__construct($db, 'Etudiant', 'Id_Etudiant');
    }

    /**
     * Obtient les informations complètes d'un étudiant
     *
     * @param int $studentId ID de l'étudiant
     * @return array|null Données de l'étudiant ou null si non trouvé
     */
    public function getStudentInfo($studentId) {
        $query = '
            SELECT 
                e.Id_Etudiant, 
                u.Id_Utilisateur,
                u.Nom_Utilisateur, 
                u.Prenom_Utilisateur, 
                u.Email_Utilisateur, 
                p.Nom_Promotion,
                p.Id_Promotion,
                c.Nom_Campus,
                c.Id_Campus
            FROM Etudiant e
            JOIN Utilisateur u ON e.Id_Utilisateur = u.Id_Utilisateur
            LEFT JOIN Appartenir a ON e.Id_Etudiant = a.Id_Etudiant
            LEFT JOIN Promotion p ON a.Id_Promotion = p.Id_Promotion
            LEFT JOIN Campus c ON p.Id_Campus = c.Id_Campus
            WHERE e.Id_Etudiant = :studentId
            ORDER BY a.Date_Debut_Appartenir DESC
            LIMIT 1
        ';

        $conn = $this->db->connect();
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':studentId', $studentId);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtient les compétences d'un étudiant
     *
     * @param int $studentId ID de l'étudiant
     * @return array Liste des compétences
     */
    public function getStudentSkills($studentId) {
        $query = '
            SELECT c.Id_Competence, c.Nom_Competence
            FROM Competence c
            JOIN Posseder p ON c.Id_Competence = p.Id_Competence
            WHERE p.Id_Etudiant = :studentId
        ';

        $conn = $this->db->connect();
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':studentId', $studentId);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtient les candidatures d'un étudiant avec détails complets
     *
     * @param int $studentId ID de l'étudiant
     * @return array Liste des candidatures avec détails
     */
    public function getStudentApplications($studentId) {
        $query = '
            SELECT 
                c.Id_Candidature, 
                c.Date_Candidature,
                o.Id_Offre, 
                o.Titre_Offre, 
                o.Description_Offre,
                o.Remuneration_Offre,
                o.Niveau_Requis_Offre,
                o.Date_Debut_Offre,
                o.Duree_Min_Offre,
                o.Duree_Max_Offre,
                e.Id_Entreprise,
                e.Nom_Entreprise
            FROM Candidature c
            JOIN Offre o ON c.Id_Offre = o.Id_Offre
            JOIN Entreprise e ON o.Id_Entreprise = e.Id_Entreprise
            WHERE c.Id_Etudiant = :studentId
            ORDER BY c.Date_Candidature DESC
        ';

        $conn = $this->db->connect();
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':studentId', $studentId);
        $stmt->execute();

        $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Pour chaque candidature, récupérer les compétences de l'offre
        foreach ($applications as &$application) {
            $stmt = $conn->prepare('
                SELECT c.Id_Competence, c.Nom_Competence
                FROM Competence c
                JOIN Necessiter n ON c.Id_Competence = n.Id_Competence
                WHERE n.Id_Offre = :offerId
            ');
            $stmt->bindParam(':offerId', $application['Id_Offre']);
            $stmt->execute();
            $application['skills'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return $applications;
    }

    /**
     * Obtient les offres souhaitées par un étudiant
     *
     * @param int $studentId ID de l'étudiant
     * @return array Liste des offres en wishlist avec détails
     */
    public function getStudentWishlist($studentId) {
        $query = '
            SELECT 
                o.Id_Offre, 
                o.Titre_Offre, 
                o.Description_Offre,
                o.Remuneration_Offre,
                o.Niveau_Requis_Offre,
                o.Date_Debut_Offre,
                o.Duree_Min_Offre, 
                o.Duree_Max_Offre,
                e.Id_Entreprise,
                e.Nom_Entreprise
            FROM Souhaiter s
            JOIN Offre o ON s.Id_Offre = o.Id_Offre
            JOIN Entreprise e ON o.Id_Entreprise = e.Id_Entreprise
            WHERE s.Id_Etudiant = :studentId
        ';

        $conn = $this->db->connect();
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':studentId', $studentId);
        $stmt->execute();

        $wishlist = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Pour chaque offre, récupérer les compétences
        foreach ($wishlist as &$offer) {
            $stmt = $conn->prepare('
                SELECT c.Id_Competence, c.Nom_Competence
                FROM Competence c
                JOIN Necessiter n ON c.Id_Competence = n.Id_Competence
                WHERE n.Id_Offre = :offerId
            ');
            $stmt->bindParam(':offerId', $offer['Id_Offre']);
            $stmt->execute();
            $offer['skills'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return $wishlist;
    }

    /**
     * Ajoute une offre à la wishlist d'un étudiant
     *
     * @param int $studentId ID de l'étudiant
     * @param int $offerId ID de l'offre
     * @return bool Succès de l'opération
     */
    public function addToWishlist($studentId, $offerId) {
        $conn = $this->db->connect();

        try {
            // Vérifier si l'offre est déjà dans la wishlist
            $stmt = $conn->prepare('
                SELECT COUNT(*) FROM Souhaiter
                WHERE Id_Etudiant = :studentId AND Id_Offre = :offerId
            ');
            $stmt->bindParam(':studentId', $studentId);
            $stmt->bindParam(':offerId', $offerId);
            $stmt->execute();

            if ($stmt->fetchColumn() > 0) {
                return true; // Déjà dans la wishlist
            }

            // Ajouter à la wishlist
            $stmt = $conn->prepare('
                INSERT INTO Souhaiter (Id_Etudiant, Id_Offre)
                VALUES (:studentId, :offerId)
            ');
            $stmt->bindParam(':studentId', $studentId);
            $stmt->bindParam(':offerId', $offerId);

            return $stmt->execute();
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Retire une offre de la wishlist d'un étudiant
     *
     * @param int $studentId ID de l'étudiant
     * @param int $offerId ID de l'offre
     * @return bool Succès de l'opération
     */
    public function removeFromWishlist($studentId, $offerId) {
        $conn = $this->db->connect();

        try {
            $stmt = $conn->prepare('
                DELETE FROM Souhaiter
                WHERE Id_Etudiant = :studentId AND Id_Offre = :offerId
            ');
            $stmt->bindParam(':studentId', $studentId);
            $stmt->bindParam(':offerId', $offerId);

            return $stmt->execute();
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Crée une candidature pour un étudiant
     *
     * @param int $studentId ID de l'étudiant
     * @param int $offerId ID de l'offre
     * @return int|null ID de la candidature, null si erreur
     */
    public function applyToOffer($studentId, $offerId) {
        $conn = $this->db->connect();

        try {
            $conn->beginTransaction();

            // Vérifier si l'étudiant a déjà postulé à cette offre
            $stmt = $conn->prepare('
                SELECT COUNT(*) FROM Candidature
                WHERE Id_Etudiant = :studentId AND Id_Offre = :offerId
            ');
            $stmt->bindParam(':studentId', $studentId);
            $stmt->bindParam(':offerId', $offerId);
            $stmt->execute();

            if ($stmt->fetchColumn() > 0) {
                return null; // Déjà postulé
            }

            // Créer la candidature
            $date = date('Y-m-d H:i:s');
            $stmt = $conn->prepare('
                INSERT INTO Candidature (Id_Etudiant, Id_Offre, Date_Candidature)
                VALUES (:studentId, :offerId, :date)
            ');
            $stmt->bindParam(':studentId', $studentId);
            $stmt->bindParam(':offerId', $offerId);
            $stmt->bindParam(':date', $date);
            $stmt->execute();

            $candidatureId = $conn->lastInsertId();

            $conn->commit();

            return $candidatureId;
        } catch (\Exception $e) {
            $conn->rollBack();
            error_log($e->getMessage());
            return null;
        }
    }

    /**
     * Obtient l'ID de l'étudiant à partir de l'ID utilisateur
     *
     * @param int $userId ID de l'utilisateur
     * @return int|null ID de l'étudiant ou null si non trouvé
     */
    public function getStudentIdFromUserId($userId) {
        $conn = $this->db->connect();
        $stmt = $conn->prepare('SELECT Id_Etudiant FROM Etudiant WHERE Id_Utilisateur = :userId');
        $stmt->bindParam(':userId', $userId);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? $result['Id_Etudiant'] : null;
    }

    /**
     * Récupère tous les étudiants avec pagination
     *
     * @param int $limit Limite de résultats
     * @param int $offset Offset pour pagination
     * @return array Liste des étudiants
     */
    public function getAllStudents($limit = 10, $offset = 0) {
        $query = '
            SELECT 
                e.Id_Etudiant, 
                u.Nom_Utilisateur, 
                u.Prenom_Utilisateur,
                pr.Nom_Promotion,
                c.Nom_Campus,
                (SELECT COUNT(*) FROM Candidature WHERE Id_Etudiant = e.Id_Etudiant) as application_count,
                (SELECT COUNT(*) FROM Souhaiter WHERE Id_Etudiant = e.Id_Etudiant) as wishlist_count
            FROM Etudiant e
            JOIN Utilisateur u ON e.Id_Utilisateur = u.Id_Utilisateur
            LEFT JOIN Appartenir a ON e.Id_Etudiant = a.Id_Etudiant
            LEFT JOIN Promotion pr ON a.Id_Promotion = pr.Id_Promotion
            LEFT JOIN Campus c ON pr.Id_Campus = c.Id_Campus
            GROUP BY e.Id_Etudiant, u.Nom_Utilisateur, u.Prenom_Utilisateur
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
}