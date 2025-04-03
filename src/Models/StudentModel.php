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
                u.Telephone_Utilisateur,
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
                e.Nom_Entreprise,
                l.Ville_Localisation,
                l.Code_Postal_Localisation
            FROM Candidature c
            JOIN Offre o ON c.Id_Offre = o.Id_Offre
            JOIN Entreprise e ON o.Id_Entreprise = e.Id_Entreprise
            JOIN Localisation l ON e.Id_Localisation = l.Id_Localisation
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

            // Récupérer les fichiers associés à la candidature (CV, LM, etc.)
            $stmt = $conn->prepare('
                SELECT f.Id_Fichier, f.Type_Fichier, f.Nom_Affichage_Fichier, f.Chemin_Fichier
                FROM Fichier f
                JOIN Contenir c ON f.Id_Fichier = c.Id_Fichier
                WHERE c.Id_Candidature = :candidatureId
            ');
            $stmt->bindParam(':candidatureId', $application['Id_Candidature']);
            $stmt->execute();
            $application['fichiers'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                e.Nom_Entreprise,
                l.Ville_Localisation,
                l.Code_Postal_Localisation
            FROM Souhaiter s
            JOIN Offre o ON s.Id_Offre = o.Id_Offre
            JOIN Entreprise e ON o.Id_Entreprise = e.Id_Entreprise
            JOIN Localisation l ON e.Id_Localisation = l.Id_Localisation
            WHERE s.Id_Etudiant = :studentId
            ORDER BY o.Date_Debut_Offre DESC
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
                $conn->rollBack();
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
     * Associe un fichier à une candidature
     *
     * @param int $candidatureId ID de la candidature
     * @param int $fileId ID du fichier
     * @return bool Succès de l'opération
     */
    public function attachFileToCandidature($candidatureId, $fileId) {
        $conn = $this->db->connect();

        try {
            $stmt = $conn->prepare('
                INSERT INTO Contenir (Id_Candidature, Id_Fichier)
                VALUES (:candidatureId, :fileId)
            ');
            $stmt->bindParam(':candidatureId', $candidatureId);
            $stmt->bindParam(':fileId', $fileId);

            return $stmt->execute();
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return false;
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
     * Récupère les informations académiques complètes d'un étudiant
     *
     * @param int $studentId ID de l'étudiant
     * @return array Données académiques incluant promotion et campus actuels
     */
    public function getStudentAcademicInfo($studentId) {
        $conn = $this->db->connect();

        $query = "
        SELECT 
            s.Id_Etudiant,
            s.Id_Utilisateur,
            u.Nom_Utilisateur,
            u.Prenom_Utilisateur,
            u.Email_Utilisateur,
            u.Telephone_Utilisateur,
            a.Id_Promotion,
            a.Date_Debut_Appartenir,
            a.Date_Fin_Appartenir,
            p.Nom_Promotion,
            p.Specialite_Promotion,
            p.Niveau_Promotion,
            p.Id_Campus,
            c.Nom_Campus
        FROM Etudiant s
        JOIN Utilisateur u ON s.Id_Utilisateur = u.Id_Utilisateur
        LEFT JOIN Appartenir a ON s.Id_Etudiant = a.Id_Etudiant
        LEFT JOIN Promotion p ON a.Id_Promotion = p.Id_Promotion
        LEFT JOIN Campus c ON p.Id_Campus = c.Id_Campus
        WHERE s.Id_Etudiant = :studentId
        ORDER BY a.Date_Debut_Appartenir DESC
        LIMIT 1
    ";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':studentId', $studentId, PDO::PARAM_INT);
        $stmt->execute();

        $studentInfo = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$studentInfo) {
            // Récupération des informations de base si aucune appartenance n'est trouvée
            $query = "
            SELECT 
                s.Id_Etudiant,
                s.Id_Utilisateur,
                u.Nom_Utilisateur,
                u.Prenom_Utilisateur,
                u.Email_Utilisateur
            FROM Etudiant s
            JOIN Utilisateur u ON s.Id_Utilisateur = u.Id_Utilisateur
            WHERE s.Id_Etudiant = :studentId
        ";

            $stmt = $conn->prepare($query);
            $stmt->bindParam(':studentId', $studentId, PDO::PARAM_INT);
            $stmt->execute();

            $studentInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        // Ajouter les compétences au résultat
        if ($studentInfo) {
            $studentInfo['skills'] = $this->getStudentSkills($studentId);
        }

        return $studentInfo;
    }

    /**
     * Met à jour les compétences d'un étudiant
     *
     * @param int $studentId ID de l'étudiant
     * @param array $skillIds Tableau d'IDs de compétences
     * @return bool Succès de l'opération
     */
    public function updateStudentSkills($studentId, array $skillIds) {
        try {
            $conn = $this->db->connect();
            $conn->beginTransaction();

            // 1. Supprimer toutes les compétences actuelles
            $stmt = $conn->prepare('
            DELETE FROM Posseder 
            WHERE Id_Etudiant = :studentId
        ');
            $stmt->bindParam(':studentId', $studentId);
            $stmt->execute();

            // 2. Ajouter les nouvelles compétences
            if (!empty($skillIds)) {
                foreach ($skillIds as $skillId) {
                    if (!empty($skillId)) {
                        $stmt = $conn->prepare('
                        INSERT INTO Posseder (
                            Id_Etudiant, 
                            Id_Competence
                        ) VALUES (
                            :studentId, 
                            :skillId
                        )
                    ');
                        $stmt->bindParam(':studentId', $studentId);
                        $stmt->bindParam(':skillId', $skillId);
                        $stmt->execute();
                    }
                }
            }

            $conn->commit();
            return true;
        } catch (\Exception $e) {
            if (isset($conn) && $conn->inTransaction()) {
                $conn->rollBack();
            }
            error_log('Erreur lors de la mise à jour des compétences: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Met à jour l'appartenance d'un étudiant à une promotion
     *
     * @param int $studentId ID de l'étudiant
     * @param int $promotionId ID de la promotion
     * @param string|null $startDate Date de début (format 'Y-m-d')
     * @param string|null $endDate Date de fin (format 'Y-m-d')
     * @param bool $replaceExisting Supprimer les associations existantes
     * @return bool Succès de l'opération
     */
    public function updateStudentPromotion($studentId, $promotionId, $startDate = null, $endDate = null, $replaceExisting = true) {
        if (empty($promotionId)) {
            return false;
        }

        $startDate = $startDate ?? date('Y-m-d');
        $endDate = $endDate ?? date('Y-m-d', strtotime('+2 years'));

        try {
            $conn = $this->db->connect();
            $conn->beginTransaction();

            // Vérifier si l'association existe déjà
            $stmt = $conn->prepare('
            SELECT * FROM Appartenir 
            WHERE Id_Etudiant = :studentId 
            AND Id_Promotion = :promotionId
        ');
            $stmt->bindParam(':studentId', $studentId);
            $stmt->bindParam(':promotionId', $promotionId);
            $stmt->execute();
            $existingAssociation = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($existingAssociation) {
                // Mettre à jour l'association existante
                $stmt = $conn->prepare('
                UPDATE Appartenir 
                SET Date_Debut_Appartenir = :startDate, 
                    Date_Fin_Appartenir = :endDate 
                WHERE Id_Etudiant = :studentId 
                AND Id_Promotion = :promotionId
            ');
            } else {
                // Supprimer les anciennes associations si demandé
                if ($replaceExisting) {
                    $stmt = $conn->prepare('
                    DELETE FROM Appartenir 
                    WHERE Id_Etudiant = :studentId
                ');
                    $stmt->bindParam(':studentId', $studentId);
                    $stmt->execute();
                }

                // Créer une nouvelle association
                $stmt = $conn->prepare('
                INSERT INTO Appartenir (
                    Id_Etudiant, 
                    Id_Promotion, 
                    Date_Debut_Appartenir, 
                    Date_Fin_Appartenir
                ) VALUES (
                    :studentId, 
                    :promotionId, 
                    :startDate, 
                    :endDate
                )
            ');
            }

            $stmt->bindParam(':studentId', $studentId);
            $stmt->bindParam(':promotionId', $promotionId);
            $stmt->bindParam(':startDate', $startDate);
            $stmt->bindParam(':endDate', $endDate);
            $stmt->execute();

            $conn->commit();
            return true;
        } catch (\Exception $e) {
            if (isset($conn) && $conn->inTransaction()) {
                $conn->rollBack();
            }
            error_log('Erreur lors de la mise à jour de la promotion: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Associe un étudiant à une promotion
     *
     * @param int $studentId ID de l'étudiant
     * @param int $promotionId ID de la promotion
     * @param string $startDate Date de début (format Y-m-d)
     * @param string $endDate Date de fin (format Y-m-d)
     * @return bool Succès de l'opération
     */
    public function assignToPromotion($studentId, $promotionId, $startDate, $endDate) {
        $conn = $this->db->connect();

        try {
            // Vérifier s'il existe déjà une association
            $stmt = $conn->prepare('
            SELECT COUNT(*) FROM Appartenir 
            WHERE Id_Etudiant = :studentId AND Id_Promotion = :promotionId
        ');
            $stmt->bindParam(':studentId', $studentId);
            $stmt->bindParam(':promotionId', $promotionId);
            $stmt->execute();

            if ($stmt->fetchColumn() > 0) {
                // Mettre à jour l'association existante
                $stmt = $conn->prepare('
                UPDATE Appartenir 
                SET Date_Debut_Appartenir = :startDate, Date_Fin_Appartenir = :endDate
                WHERE Id_Etudiant = :studentId AND Id_Promotion = :promotionId
            ');
            } else {
                // Créer une nouvelle association
                $stmt = $conn->prepare('
                INSERT INTO Appartenir (Id_Etudiant, Id_Promotion, Date_Debut_Appartenir, Date_Fin_Appartenir)
                VALUES (:studentId, :promotionId, :startDate, :endDate)
            ');
            }

            $stmt->bindParam(':studentId', $studentId);
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
     * Récupère tous les étudiants sans filtrage par pilote
     *
     * @return array Liste de tous les étudiants
     */
    public function getAllStudents() {
        $query = '
        SELECT 
            e.Id_Etudiant,
            u.Id_Utilisateur,
            u.Nom_Utilisateur,
            u.Prenom_Utilisateur,
            u.Email_Utilisateur,
            p.Id_Promotion,
            p.Nom_Promotion,
            c.Id_Campus,
            c.Nom_Campus,
            (SELECT COUNT(*) FROM Candidature WHERE Id_Etudiant = e.Id_Etudiant) as application_count,
            (SELECT COUNT(*) FROM Souhaiter WHERE Id_Etudiant = e.Id_Etudiant) as wishlist_count
        FROM Etudiant e
        JOIN Utilisateur u ON e.Id_Utilisateur = u.Id_Utilisateur
        LEFT JOIN Appartenir a ON e.Id_Etudiant = a.Id_Etudiant
        LEFT JOIN Promotion p ON a.Id_Promotion = p.Id_Promotion
        LEFT JOIN Campus c ON p.Id_Campus = c.Id_Campus
        GROUP BY 
            e.Id_Etudiant, u.Id_Utilisateur, u.Nom_Utilisateur, u.Prenom_Utilisateur, 
            u.Email_Utilisateur, p.Id_Promotion, p.Nom_Promotion, c.Id_Campus, c.Nom_Campus
        ORDER BY u.Nom_Utilisateur, u.Prenom_Utilisateur
    ';


        $conn = $this->db->connect();
        $stmt = $conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}