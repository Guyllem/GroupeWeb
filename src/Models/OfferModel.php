<?php
namespace App\Models;

use PDO;

class OfferModel extends Model {
    public function __construct($db) {
        parent::__construct($db, 'Offre', 'Id_Offre');
    }

    /**
     * Obtient les offres récentes
     *
     * @param int $limit Nombre maximum d'offres à retourner
     * @param int $offset Position de départ
     * @return array Liste des offres
     */
    public function getRecentOffers($limit = 10, $offset = 0) {
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
            ORDER BY o.Date_Debut_Offre DESC
            LIMIT :limit OFFSET :offset
        ';

        $conn = $this->db->connect();
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $offers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Pour chaque offre, obtenir ses compétences
        foreach ($offers as &$offer) {
            $offer['skills'] = $this->getOfferSkills($offer['Id_Offre']);
        }

        return $offers;
    }

    /**
     * Obtient les détails d'une offre
     *
     * @param int $offerId ID de l'offre
     * @return array|null Détails de l'offre ou null si non trouvée
     */
    public function getOfferDetails($offerId) {
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
                l.Code_Postal_Localisation,
                l.Adresse_Localisation
            FROM Offre o
            JOIN Entreprise e ON o.Id_Entreprise = e.Id_Entreprise
            JOIN Localisation l ON e.Id_Localisation = l.Id_Localisation
            WHERE o.Id_Offre = :offerId
        ';

        $conn = $this->db->connect();
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':offerId', $offerId);
        $stmt->execute();

        $offer = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$offer) {
            return null;
        }

        // Obtenir les compétences requises
        $offer['skills'] = $this->getOfferSkills($offerId);

        return $offer;
    }

    /**
     * Vérifie si une offre est dans la wishlist d'un étudiant
     *
     * @param int $offerId ID de l'offre
     * @param int $studentId ID de l'étudiant
     * @return bool True si dans la wishlist, false sinon
     */
    public function isInWishlist($offerId, $studentId) {
        $conn = $this->db->connect();
        $stmt = $conn->prepare('
            SELECT COUNT(*) FROM Souhaiter
            WHERE Id_Offre = :offerId AND Id_Etudiant = :studentId
        ');
        $stmt->bindParam(':offerId', $offerId);
        $stmt->bindParam(':studentId', $studentId);
        $stmt->execute();

        return $stmt->fetchColumn() > 0;
    }

    /**
     * Obtient les compétences requises pour une offre
     *
     * @param int $offerId ID de l'offre
     * @return array Liste des compétences
     */
    public function getOfferSkills($offerId) {
        $conn = $this->db->connect();
        $stmt = $conn->prepare('
            SELECT c.Id_Competence, c.Nom_Competence
            FROM Competence c
            JOIN Necessiter n ON c.Id_Competence = n.Id_Competence
            WHERE n.Id_Offre = :offerId
        ');
        $stmt->bindParam(':offerId', $offerId);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Crée une nouvelle offre
     *
     * @param array $offerData Données de l'offre
     * @param array $skillIds IDs des compétences requises
     * @return int|null ID de la nouvelle offre, null si erreur
     */
    public function createOffer($offerData, $skillIds = []) {
        $conn = $this->db->connect();

        try {
            $conn->beginTransaction();

            // Créer l'offre
            $stmt = $conn->prepare('
                INSERT INTO Offre (
                    Titre_Offre, 
                    Description_Offre, 
                    Remuneration_Offre,
                    Niveau_Requis_Offre, 
                    Date_Debut_Offre, 
                    Duree_Min_Offre, 
                    Duree_Max_Offre,
                    Id_Entreprise
                ) VALUES (
                    :titre, 
                    :description, 
                    :remuneration,
                    :niveauRequis, 
                    :dateDebut, 
                    :dureeMin, 
                    :dureeMax,
                    :idEntreprise
                )
            ');

            $stmt->bindParam(':titre', $offerData['titre']);
            $stmt->bindParam(':description', $offerData['description']);
            $stmt->bindParam(':remuneration', $offerData['remuneration']);
            $stmt->bindParam(':niveauRequis', $offerData['niveauRequis']);
            $stmt->bindParam(':dateDebut', $offerData['dateDebut']);
            $stmt->bindParam(':dureeMin', $offerData['dureeMin']);
            $stmt->bindParam(':dureeMax', $offerData['dureeMax']);
            $stmt->bindParam(':idEntreprise', $offerData['idEntreprise']);

            $stmt->execute();

            $offerId = $conn->lastInsertId();

            // Ajouter les compétences requises
            if (!empty($skillIds)) {
                $insertSkillQuery = 'INSERT INTO Necessiter (Id_Offre, Id_Competence) VALUES ';
                $values = [];

                foreach ($skillIds as $skillId) {
                    $values[] = "({$offerId}, {$skillId})";
                }

                $insertSkillQuery .= implode(', ', $values);
                $conn->exec($insertSkillQuery);
            }

            $conn->commit();

            return $offerId;
        } catch (\Exception $e) {
            $conn->rollBack();
            error_log($e->getMessage());
            return null;
        }
    }

    /**
     * Met à jour une offre existante
     *
     * @param int $offerId ID de l'offre
     * @param array $offerData Données de l'offre
     * @param array $skillIds IDs des compétences requises
     * @return bool Succès de l'opération
     */
    public function updateOffer($offerId, $offerData, $skillIds = []) {
        $conn = $this->db->connect();

        try {
            $conn->beginTransaction();

            // Mettre à jour l'offre
            $stmt = $conn->prepare('
                UPDATE Offre SET
                    Titre_Offre = :titre, 
                    Description_Offre = :description, 
                    Remuneration_Offre = :remuneration,
                    Niveau_Requis_Offre = :niveauRequis, 
                    Date_Debut_Offre = :dateDebut, 
                    Duree_Min_Offre = :dureeMin, 
                    Duree_Max_Offre = :dureeMax,
                    Id_Entreprise = :idEntreprise
                WHERE Id_Offre = :offerId
            ');

            $stmt->bindParam(':titre', $offerData['titre']);
            $stmt->bindParam(':description', $offerData['description']);
            $stmt->bindParam(':remuneration', $offerData['remuneration']);
            $stmt->bindParam(':niveauRequis', $offerData['niveauRequis']);
            $stmt->bindParam(':dateDebut', $offerData['dateDebut']);
            $stmt->bindParam(':dureeMin', $offerData['dureeMin']);
            $stmt->bindParam(':dureeMax', $offerData['dureeMax']);
            $stmt->bindParam(':idEntreprise', $offerData['idEntreprise']);
            $stmt->bindParam(':offerId', $offerId);

            $stmt->execute();

            // Mettre à jour les compétences requises
            // D'abord supprimer les anciennes compétences
            $stmt = $conn->prepare('DELETE FROM Necessiter WHERE Id_Offre = :offerId');
            $stmt->bindParam(':offerId', $offerId);
            $stmt->execute();

            // Ajouter les nouvelles compétences
            if (!empty($skillIds)) {
                $insertSkillQuery = 'INSERT INTO Necessiter (Id_Offre, Id_Competence) VALUES ';
                $values = [];

                foreach ($skillIds as $skillId) {
                    $values[] = "({$offerId}, {$skillId})";
                }

                $insertSkillQuery .= implode(', ', $values);
                $conn->exec($insertSkillQuery);
            }

            $conn->commit();

            return true;
        } catch (\Exception $e) {
            $conn->rollBack();
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Supprime une offre
     *
     * @param int $offerId ID de l'offre
     * @return bool Succès de l'opération
     */
    public function deleteOffer($offerId) {
        $conn = $this->db->connect();

        try {
            $conn->beginTransaction();

            // Supprimer les dépendances
            $conn->exec("DELETE FROM Necessiter WHERE Id_Offre = {$offerId}");
            $conn->exec("DELETE FROM Souhaiter WHERE Id_Offre = {$offerId}");
            $conn->exec("DELETE FROM Candidature WHERE Id_Offre = {$offerId}");

            // Supprimer l'offre
            $stmt = $conn->prepare('DELETE FROM Offre WHERE Id_Offre = :offerId');
            $stmt->bindParam(':offerId', $offerId);
            $stmt->execute();

            $conn->commit();

            return true;
        } catch (\Exception $e) {
            $conn->rollBack();
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Recherche des offres selon des critères
     *
     * @param array $criteria Critères de recherche
     * @return array Liste des offres correspondant aux critères
     */
    public function searchOffers($criteria = []) {
        $query = '
            SELECT 
                o.Id_Offre, 
                o.Titre_Offre, 
                o.Description_Offre,
                o.Remuneration_Offre,
                o.Niveau_Requis_Offre,
                o.Date_Debut_Offre,
                e.Nom_Entreprise,
                l.Ville_Localisation
            FROM Offre o
            JOIN Entreprise e ON o.Id_Entreprise = e.Id_Entreprise
            JOIN Localisation l ON e.Id_Localisation = l.Id_Localisation
        ';

        $whereConditions = [];
        $params = [];

        // Construire les conditions de recherche
        if (!empty($criteria['titre'])) {
            $whereConditions[] = 'o.Titre_Offre LIKE :titre';
            $params['titre'] = '%' . $criteria['titre'] . '%';
        }

        if (!empty($criteria['ville'])) {
            $whereConditions[] = 'l.Ville_Localisation LIKE :ville';
            $params['ville'] = '%' . $criteria['ville'] . '%';
        }

        if (!empty($criteria['entreprise'])) {
            $whereConditions[] = 'e.Nom_Entreprise LIKE :entreprise';
            $params['entreprise'] = '%' . $criteria['entreprise'] . '%';
        }

        if (!empty($criteria['competence'])) {
            $query .= ' JOIN Necessiter n ON o.Id_Offre = n.Id_Offre
                        JOIN Competence c ON n.Id_Competence = c.Id_Competence';
            $whereConditions[] = 'c.Nom_Competence LIKE :competence';
            $params['competence'] = '%' . $criteria['competence'] . '%';
        }

        if (!empty($criteria['minRemuneration'])) {
            $whereConditions[] = 'o.Remuneration_Offre >= :minRemuneration';
            $params['minRemuneration'] = $criteria['minRemuneration'];
        }

        if (!empty($criteria['minDuree'])) {
            $whereConditions[] = 'o.Duree_Min_Offre >= :minDuree';
            $params['minDuree'] = $criteria['minDuree'];
        }

        if (!empty($criteria['maxDuree'])) {
            $whereConditions[] = 'o.Duree_Max_Offre <= :maxDuree';
            $params['maxDuree'] = $criteria['maxDuree'];
        }

        // Ajouter les conditions à la requête
        if (!empty($whereConditions)) {
            $query .= ' WHERE ' . implode(' AND ', $whereConditions);
        }

        // Ajouter GROUP BY, ORDER BY et LIMIT
        $query .= ' GROUP BY o.Id_Offre';

        if (!empty($criteria['orderBy'])) {
            switch ($criteria['orderBy']) {
                case 'recent':
                    $query .= ' ORDER BY o.Date_Debut_Offre DESC';
                    break;
                case 'ancien':
                    $query .= ' ORDER BY o.Date_Debut_Offre ASC';
                    break;
                case 'remuneration':
                    $query .= ' ORDER BY o.Remuneration_Offre DESC';
                    break;
                default:
                    $query .= ' ORDER BY o.Date_Debut_Offre DESC';
                    break;
            }
        } else {
            $query .= ' ORDER BY o.Date_Debut_Offre DESC';
        }

        if (!empty($criteria['limit'])) {
            $query .= ' LIMIT :limit';
            $params['limit'] = (int) $criteria['limit'];

            if (!empty($criteria['offset'])) {
                $query .= ' OFFSET :offset';
                $params['offset'] = (int) $criteria['offset'];
            }
        }

        $conn = $this->db->connect();
        $stmt = $conn->prepare($query);

        foreach ($params as $key => $value) {
            if (in_array($key, ['limit', 'offset', 'minRemuneration', 'minDuree', 'maxDuree'])) {
                $stmt->bindValue(":{$key}", $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue(":{$key}", $value, PDO::PARAM_STR);
            }
        }

        $stmt->execute();
        $offers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Pour chaque offre, obtenir ses compétences
        foreach ($offers as &$offer) {
            $offer['skills'] = $this->getOfferSkills($offer['Id_Offre']);
        }

        return $offers;
    }
}