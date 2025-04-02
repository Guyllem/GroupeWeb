<?php
namespace App\Models;

use PDO;
use App\Utils\SecurityUtil;

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
            MAX(c.Nom_Campus) as Nom_Campus,
            MAX(c.Id_Campus) as Id_Campus,
            COUNT(DISTINCT s.Id_Promotion) as promotion_count
        FROM Pilote p
        JOIN Utilisateur u ON p.Id_Utilisateur = u.Id_Utilisateur
        LEFT JOIN Superviser s ON p.Id_Pilote = s.Id_Pilote
        LEFT JOIN Promotion pr ON s.Id_Promotion = pr.Id_Promotion
        LEFT JOIN Campus c ON pr.Id_Campus = c.Id_Campus
        GROUP BY p.Id_Pilote, u.Id_Utilisateur, u.Nom_Utilisateur, u.Prenom_Utilisateur
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
            MAX(c.Nom_Campus) as Nom_Campus,  /* Utilisation de MAX() pour agréger */
            MAX(c.Id_Campus) as Id_Campus     /* Utilisation de MAX() pour agréger */
        FROM Pilote p
        JOIN Utilisateur u ON p.Id_Utilisateur = u.Id_Utilisateur
        LEFT JOIN Superviser s ON p.Id_Pilote = s.Id_Pilote
        LEFT JOIN Promotion pr ON s.Id_Promotion = pr.Id_Promotion
        LEFT JOIN Campus c ON pr.Id_Campus = c.Id_Campus
        WHERE p.Id_Pilote = :pilotId
        GROUP BY p.Id_Pilote, u.Id_Utilisateur, u.Nom_Utilisateur, u.Prenom_Utilisateur, u.Email_Utilisateur
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
     * Recherche des étudiants selon des critères
     *
     * @param string $searchTerm Terme de recherche (nom ou prénom)
     * @param int $pilotId ID du pilote (pour limiter aux étudiants supervisés)
     * @param int $limit Limite de résultats
     * @param int $offset Offset pour pagination
     * @return array Liste des étudiants correspondant aux critères
     */
    public function searchStudents($searchTerm, $pilotId, $limit = 10, $offset = 0) {
        $searchTerm = "%$searchTerm%";

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
            AND (u.Nom_Utilisateur LIKE :searchTerm OR u.Prenom_Utilisateur LIKE :searchTerm)
            ORDER BY u.Nom_Utilisateur, u.Prenom_Utilisateur
            LIMIT :limit OFFSET :offset
        ';

        $conn = $this->db->connect();
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':pilotId', $pilotId);
        $stmt->bindParam(':searchTerm', $searchTerm);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
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

    /**
     * Met à jour le mot de passe d'un étudiant
     *
     * @param int $userId ID de l'utilisateur
     * @param string $newPassword Nouveau mot de passe en clair
     * @return bool Succès de l'opération
     */
    public function updateStudentPassword($userId, $newPassword) {
        $conn = $this->db->connect();

        try {
            $hashedPassword = SecurityUtil::hashPassword($newPassword);

            $stmt = $conn->prepare('
                UPDATE Utilisateur 
                SET Password_Utilisateur = :password 
                WHERE Id_Utilisateur = :userId
            ');

            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':userId', $userId);

            return $stmt->execute();
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Évalue une entreprise
     *
     * @param int $enterpriseId ID de l'entreprise
     * @param int $userId ID de l'utilisateur qui évalue
     * @param int $rating Note (1-5)
     * @return bool Succès de l'opération
     */
    public function rateEnterprise($enterpriseId, $userId, $rating) {
        $conn = $this->db->connect();

        try {
            // Vérifier si l'utilisateur a déjà évalué cette entreprise
            $stmt = $conn->prepare('
                SELECT COUNT(*) FROM Evaluer
                WHERE Id_Entreprise = :enterpriseId AND Id_Utilisateur = :userId
            ');
            $stmt->bindParam(':enterpriseId', $enterpriseId);
            $stmt->bindParam(':userId', $userId);
            $stmt->execute();

            if ($stmt->fetchColumn() > 0) {
                // Mettre à jour l'évaluation existante
                $stmt = $conn->prepare('
                    UPDATE Evaluer
                    SET Note_Evaluer = :rating
                    WHERE Id_Entreprise = :enterpriseId AND Id_Utilisateur = :userId
                ');
            } else {
                // Créer une nouvelle évaluation
                $stmt = $conn->prepare('
                    INSERT INTO Evaluer (Id_Entreprise, Id_Utilisateur, Note_Evaluer)
                    VALUES (:enterpriseId, :userId, :rating)
                ');
            }

            $stmt->bindParam(':enterpriseId', $enterpriseId);
            $stmt->bindParam(':userId', $userId);
            $stmt->bindParam(':rating', $rating);

            return $stmt->execute();
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Recherche des offres selon des critères
     *
     * @param string $searchTerm Terme de recherche (titre ou description)
     * @param int $limit Limite de résultats
     * @param int $offset Offset pour pagination
     * @return array Liste des offres correspondant aux critères
     */
    public function searchOffers($searchTerm, $limit = 10, $offset = 0) {
        $searchTerm = "%$searchTerm%";

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
            FROM Offre o
            JOIN Entreprise e ON o.Id_Entreprise = e.Id_Entreprise
            WHERE o.Titre_Offre LIKE :searchTerm OR o.Description_Offre LIKE :searchTerm
            ORDER BY o.Date_Debut_Offre DESC
            LIMIT :limit OFFSET :offset
        ';

        $conn = $this->db->connect();
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':searchTerm', $searchTerm);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $offers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Pour chaque offre, obtenir ses compétences
        foreach ($offers as &$offer) {
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

        return $offers;
    }

    /**
     * Recherche des entreprises selon des critères
     *
     * @param string $searchTerm Terme de recherche (nom ou description)
     * @param int $limit Limite de résultats
     * @param int $offset Offset pour pagination
     * @return array Liste des entreprises correspondant aux critères
     */
    public function searchEnterprises($searchTerm, $limit = 10, $offset = 0) {
        $searchTerm = "%$searchTerm%";

        $query = '
            SELECT 
                e.Id_Entreprise, 
                e.Nom_Entreprise, 
                e.Description_Entreprise,
                AVG(ev.Note_Evaluer) as rating,
                (SELECT COUNT(*) FROM Offre WHERE Id_Entreprise = e.Id_Entreprise) as offer_count
            FROM Entreprise e
            LEFT JOIN Evaluer ev ON e.Id_Entreprise = ev.Id_Entreprise
            WHERE e.Nom_Entreprise LIKE :searchTerm OR e.Description_Entreprise LIKE :searchTerm
            GROUP BY e.Id_Entreprise, e.Nom_Entreprise
            ORDER BY e.Nom_Entreprise
            LIMIT :limit OFFSET :offset
        ';

        $conn = $this->db->connect();
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':searchTerm', $searchTerm);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $enterprises = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Pour chaque entreprise, obtenir ses secteurs
        foreach ($enterprises as &$enterprise) {
            $stmt = $conn->prepare('
                SELECT s.Id_Secteur, s.Nom_Secteur
                FROM Secteur s
                JOIN Fournir f ON s.Id_Secteur = f.Id_Secteur
                WHERE f.Id_Entreprise = :enterpriseId
            ');
            $stmt->bindParam(':enterpriseId', $enterprise['Id_Entreprise']);
            $stmt->execute();
            $enterprise['sectors'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return $enterprises;
    }

    /**
     * Obtient les offres d'une entreprise
     *
     * @param int $enterpriseId ID de l'entreprise
     * @return array Liste des offres de l'entreprise
     */
    public function getEnterpriseOffers($enterpriseId) {
        $query = '
            SELECT 
                o.Id_Offre, 
                o.Titre_Offre, 
                o.Description_Offre,
                o.Remuneration_Offre,
                o.Niveau_Requis_Offre,
                o.Date_Debut_Offre,
                o.Duree_Min_Offre,
                o.Duree_Max_Offre
            FROM Offre o
            WHERE o.Id_Entreprise = :enterpriseId
            ORDER BY o.Date_Debut_Offre DESC
        ';

        $conn = $this->db->connect();
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':enterpriseId', $enterpriseId);
        $stmt->execute();

        $offers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Pour chaque offre, obtenir ses compétences
        foreach ($offers as &$offer) {
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

        return $offers;
    }
}