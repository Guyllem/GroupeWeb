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
                e.Nom_Entreprise,
                l.Ville_Localisation,
                l.Code_Postal_Localisation
            FROM Offre o
            JOIN Entreprise e ON o.Id_Entreprise = e.Id_Entreprise
            JOIN Localisation l ON e.Id_Localisation = l.Id_Localisation
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
     * Récupère toutes les compétences disponibles dans la base de données
     *
     * @param string $orderBy Colonne de tri (par défaut: Nom_Competence)
     * @param string $direction Direction du tri (ASC ou DESC)
     * @return array Liste des compétences
     */
    public function getAllCompetences() {
        $query = '
        SELECT 
            Id_Competence, 
            Nom_Competence
        FROM Competence
        ';

        $conn = $this->db->connect();
        $stmt = $conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Vérifie si une offre est dans la wishlist d'un étudiant
     *
     * @param int $offerId ID de l'offre
     * @param int $studentId ID de l'étudiant
     * @return bool True si dans la wishlist, false sinon
     */

    /**
     * Récupère les offres associées à une entreprise spécifique
     *
     * @param int $enterpriseId ID de l'entreprise
     * @return array Liste des offres
     */
    public function getOffersByEnterprise($enterpriseId) {
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

        $offers = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Pour chaque offre, récupérer les compétences requises
        foreach ($offers as &$offer) {
            $offer['skills'] = $this->getOfferSkills($offer['Id_Offre']);
        }

        return $offers;
    }

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
                o.Duree_Min_Offre,
                o.Duree_Max_Offre,
                e.Id_Entreprise,
                e.Nom_Entreprise,
                l.Ville_Localisation,
                l.Code_Postal_Localisation
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

        if (!empty($criteria['competence'])) {
            $query .= ' JOIN Necessiter n ON o.Id_Offre = n.Id_Offre
                        JOIN Competence c ON n.Id_Competence = c.Id_Competence';
            $whereConditions[] = 'c.Nom_Competence LIKE :competence';
            $params['competence'] = '%' . $criteria['competence'] . '%';
        }

        if (!empty($criteria['ville'])) {
            $whereConditions[] = 'l.Ville_Localisation LIKE :ville';
            $params['ville'] = '%' . $criteria['ville'] . '%';
        }

        if (!empty($criteria['entreprise'])) {
            $whereConditions[] = 'e.Nom_Entreprise LIKE :entreprise';
            $params['entreprise'] = '%' . $criteria['entreprise'] . '%';
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

        // Ajouter GROUP BY pour éviter les doublons si JOIN avec compétences
        if (!empty($criteria['competence'])) {
            $query .= ' GROUP BY o.Id_Offre';
        }

        // Ajouter ORDER BY
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

        // Ajouter LIMIT et OFFSET
        if (isset($criteria['limit'])) {
            $query .= ' LIMIT :limit';
            if (isset($criteria['offset'])) {
                $query .= ' OFFSET :offset';
            }
        }

        $conn = $this->db->connect();
        $stmt = $conn->prepare($query);

        foreach ($params as $param => $value) {
            $stmt->bindValue(":{$param}", $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }

        if (isset($criteria['limit'])) {
            $stmt->bindValue(':limit', $criteria['limit'], PDO::PARAM_INT);
            if (isset($criteria['offset'])) {
                $stmt->bindValue(':offset', $criteria['offset'], PDO::PARAM_INT);
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