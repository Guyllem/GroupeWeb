<?php
namespace App\Models;

use PDO;

class EnterpriseModel extends Model {
    public function __construct($db) {
        parent::__construct($db, 'Entreprise', 'Id_Entreprise');
    }

    /**
     * Obtient les entreprises les mieux notées
     *
     * @param int $limit Nombre maximum d'entreprises à retourner
     * @param int $offset Position de départ
     * @return array Liste des entreprises
     */
    public function getTopRatedEnterprises($limit = 10, $offset = 0) {
        $query = '
            SELECT 
                e.Id_Entreprise, 
                e.Nom_Entreprise, 
                e.Description_Entreprise,
                e.Email_Entreprise,
                e.Telephone_Entreprise,
                e.Effectif_Entreprise,
                AVG(ev.Note_Evaluer) as rating
            FROM Entreprise e
            LEFT JOIN Evaluer ev ON e.Id_Entreprise = ev.Id_Entreprise
            GROUP BY e.Id_Entreprise, e.Nom_Entreprise
            ORDER BY rating DESC, e.Nom_Entreprise ASC
            LIMIT :limit OFFSET :offset
        ';

        $conn = $this->db->connect();
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $enterprises = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Pour chaque entreprise, obtenir ses secteurs (comme tags)
        foreach ($enterprises as &$enterprise) {
            $enterprise['tags'] = $this->getEnterpriseSectors($enterprise['Id_Entreprise']);
        }

        return $enterprises;
    }

    /**
     * Obtient les entreprises par ordre alphabétique
     *
     * @param int $limit Nombre maximum d'entreprises à retourner
     * @param int $offset Position de départ
     * @return array Liste des entreprises
     */
    /**
     * Obtient les entreprises par ordre alphabétique avec informations complètes
     *
     * @param int $limit Nombre maximum d'entreprises à retourner
     * @param int $offset Position de départ
     * @return array Liste des entreprises
     */
    public function getEnterprisesByName($limit = 10, $offset = 0) {
        $query = '
        SELECT 
            e.Id_Entreprise, 
            e.Nom_Entreprise, 
            e.Description_Entreprise,
            e.Email_Entreprise,
            e.Telephone_Entreprise,
            e.Effectif_Entreprise,
            AVG(ev.Note_Evaluer) as rating,
            (SELECT COUNT(*) FROM Offre o WHERE o.Id_Entreprise = e.Id_Entreprise) as offer_count
        FROM Entreprise e
        LEFT JOIN Evaluer ev ON e.Id_Entreprise = ev.Id_Entreprise
        GROUP BY e.Id_Entreprise, e.Nom_Entreprise, e.Description_Entreprise, 
                 e.Email_Entreprise, e.Telephone_Entreprise, e.Effectif_Entreprise
        ORDER BY e.Nom_Entreprise
        LIMIT :limit OFFSET :offset
    ';

        $conn = $this->db->connect();
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $enterprises = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Pour chaque entreprise, obtenir ses secteurs (comme tags)
        foreach ($enterprises as &$enterprise) {
            $enterprise['tags'] = $this->getEnterpriseSectors($enterprise['Id_Entreprise']);
        }

        return $enterprises;
    }

    /**
     * Obtient les détails d'une entreprise
     *
     * @param int $enterpriseId ID de l'entreprise
     * @return array|null Détails de l'entreprise ou null si non trouvée
     */
    public function getEnterpriseDetails($enterpriseId) {
        $query = '
            SELECT 
                e.Id_Entreprise, 
                e.Nom_Entreprise, 
                e.Description_Entreprise,
                e.Email_Entreprise,
                e.Telephone_Entreprise,
                e.Effectif_Entreprise,
                l.Id_Localisation,
                l.Ville_Localisation,
                l.Code_Postal_Localisation,
                l.Adresse_Localisation,
                AVG(ev.Note_Evaluer) as rating,
                COUNT(ev.Id_Utilisateur) as rating_count
            FROM Entreprise e
            LEFT JOIN Localisation l ON e.Id_Localisation = l.Id_Localisation
            LEFT JOIN Evaluer ev ON e.Id_Entreprise = ev.Id_Entreprise
            WHERE e.Id_Entreprise = :enterpriseId
            GROUP BY e.Id_Entreprise
        ';

        $conn = $this->db->connect();
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':enterpriseId', $enterpriseId);
        $stmt->execute();

        $enterprise = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$enterprise) {
            return null;
        }

        // Obtenir les secteurs (comme tags)
        $enterprise['tags'] = $this->getEnterpriseSectors($enterpriseId);

        // Obtenir le nombre d'offres de l'entreprise
        $stmt = $conn->prepare('
            SELECT COUNT(*) FROM Offre
            WHERE Id_Entreprise = :enterpriseId
        ');
        $stmt->bindParam(':enterpriseId', $enterpriseId);
        $stmt->execute();
        $enterprise['offer_count'] = $stmt->fetchColumn();

        return $enterprise;
    }

    /**
     * Vérifie si une entreprise est aimée par un utilisateur
     *
     * @param int $enterpriseId ID de l'entreprise
     * @param int $userId ID de l'utilisateur
     * @return bool True si aimée, false sinon
     */
    public function isLikedByUser($enterpriseId, $userId) {
        $conn = $this->db->connect();
        $stmt = $conn->prepare('
            SELECT COUNT(*) FROM Evaluer
            WHERE Id_Entreprise = :enterpriseId AND Id_Utilisateur = :userId
        ');
        $stmt->bindParam(':enterpriseId', $enterpriseId);
        $stmt->bindParam(':userId', $userId);
        $stmt->execute();

        return $stmt->fetchColumn() > 0;
    }

    /**
     * Obtient les secteurs d'une entreprise
     *
     * @param int $enterpriseId ID de l'entreprise
     * @return array Liste des secteurs
     */
    public function getEnterpriseSectors($enterpriseId) {
        $conn = $this->db->connect();
        $stmt = $conn->prepare('
            SELECT s.Id_Secteur, s.Nom_Secteur
            FROM Secteur s
            JOIN Fournir f ON s.Id_Secteur = f.Id_Secteur
            WHERE f.Id_Entreprise = :enterpriseId
        ');
        $stmt->bindParam(':enterpriseId', $enterpriseId);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Évalue une entreprise
     *
     * @param int $enterpriseId ID de l'entreprise
     * @param int $userId ID de l'utilisateur
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
     * Recherche des entreprises selon des critères
     *
     * @param array $criteria Critères de recherche
     * @return array Liste des entreprises
     */
    public function searchEnterprises($criteria = []) {
        $query = '
            SELECT 
                e.Id_Entreprise, 
                e.Nom_Entreprise, 
                e.Description_Entreprise,
                e.Email_Entreprise,
                e.Telephone_Entreprise,
                e.Effectif_Entreprise,
                l.Ville_Localisation,
                l.Code_Postal_Localisation,
                AVG(ev.Note_Evaluer) as rating
            FROM Entreprise e
            LEFT JOIN Localisation l ON e.Id_Localisation = l.Id_Localisation
            LEFT JOIN Evaluer ev ON e.Id_Entreprise = ev.Id_Entreprise
        ';

        // Joindre la table Secteur si nécessaire
        if (!empty($criteria['secteur'])) {
            $query .= '
                JOIN Fournir f ON e.Id_Entreprise = f.Id_Entreprise
                JOIN Secteur s ON f.Id_Secteur = s.Id_Secteur
            ';
        }

        $whereConditions = [];
        $params = [];

        // Ajouter les conditions de recherche
        if (!empty($criteria['nom'])) {
            $whereConditions[] = 'e.Nom_Entreprise LIKE :nom';
            $params[':nom'] = '%' . $criteria['nom'] . '%';
        }

        if (!empty($criteria['description'])) {
            $whereConditions[] = 'e.Description_Entreprise LIKE :description';
            $params[':description'] = '%' . $criteria['description'] . '%';
        }

        if (!empty($criteria['secteur'])) {
            $whereConditions[] = 's.Nom_Secteur LIKE :secteur';
            $params[':secteur'] = '%' . $criteria['secteur'] . '%';
        }

        if (!empty($criteria['ville'])) {
            $whereConditions[] = 'l.Ville_Localisation LIKE :ville';
            $params[':ville'] = '%' . $criteria['ville'] . '%';
        }

        if (!empty($criteria['code_postal'])) {
            $whereConditions[] = 'l.Code_Postal_Localisation LIKE :code_postal';
            $params[':code_postal'] = '%' . $criteria['code_postal'] . '%';
        }

        // Ajouter les conditions WHERE si nécessaire
        if (!empty($whereConditions)) {
            $query .= ' WHERE ' . implode(' AND ', $whereConditions);
        }

        // Grouper par Id_Entreprise pour éviter les doublons
        $query .= ' GROUP BY e.Id_Entreprise';

        // Ajouter ORDER BY
        if (!empty($criteria['orderBy'])) {
            switch ($criteria['orderBy']) {
                case 'nom_asc':
                    $query .= ' ORDER BY e.Nom_Entreprise ASC';
                    break;
                case 'nom_desc':
                    $query .= ' ORDER BY e.Nom_Entreprise DESC';
                    break;
                case 'rating_desc':
                    $query .= ' ORDER BY rating DESC';
                    break;
                case 'ville':
                    $query .= ' ORDER BY l.Ville_Localisation ASC';
                    break;
                default:
                    $query .= ' ORDER BY rating DESC, e.Nom_Entreprise ASC';
                    break;
            }
        } else {
            $query .= ' ORDER BY rating DESC, e.Nom_Entreprise ASC';
        }

        // Ajouter LIMIT et OFFSET
        if (!empty($criteria['limit'])) {
            $query .= ' LIMIT :limit';
            $params[':limit'] = (int) $criteria['limit'];

            if (!empty($criteria['offset'])) {
                $query .= ' OFFSET :offset';
                $params[':offset'] = (int) $criteria['offset'];
            }
        }

        $conn = $this->db->connect();
        $stmt = $conn->prepare($query);

        foreach ($params as $key => $value) {
            if (in_array($key, [':limit', ':offset'])) {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value, PDO::PARAM_STR);
            }
        }

        $stmt->execute();
        $enterprises = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Pour chaque entreprise, obtenir ses secteurs (comme tags)
        foreach ($enterprises as &$enterprise) {
            $enterprise['tags'] = $this->getEnterpriseSectors($enterprise['Id_Entreprise']);
        }

        return $enterprises;
    }

    /**
     * Obtient les offres d'une entreprise
     *
     * @param int $enterpriseId ID de l'entreprise
     * @param int $limit Nombre maximum d'offres à retourner
     * @param int $offset Position de départ
     * @return array Liste des offres
     */
    public function getEnterpriseOffers($enterpriseId, $limit = 10, $offset = 0) {
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
            LIMIT :limit OFFSET :offset
        ';

        $conn = $this->db->connect();
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':enterpriseId', $enterpriseId);
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
}