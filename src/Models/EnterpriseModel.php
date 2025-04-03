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

    /**
     * Crée une nouvelle entreprise avec sa localisation et ses secteurs (format CSV)
     *
     * @param array $data Données de l'entreprise (nom, description, email, etc.)
     * @param string $secteursCsv Liste des secteurs au format CSV "Informatique,Finance,Marketing"
     * @return int|null ID de l'entreprise créée ou null en cas d'erreur
     */
    public function createEnterprise($data, $secteursCsv = '') {
        $conn = $this->db->connect();

        try {
            // Démarrer une transaction
            $conn->beginTransaction();

            // 1. Créer d'abord la localisation
            $stmtLoc = $conn->prepare('
            INSERT INTO Localisation (Ville_Localisation, Code_Postal_Localisation, Adresse_Localisation)
            VALUES (:ville, :codePostal, :adresse)
        ');
            $stmtLoc->bindParam(':ville', $data['ville']);
            $stmtLoc->bindParam(':codePostal', $data['codePostal']);
            $stmtLoc->bindParam(':adresse', $data['adresse']);
            $stmtLoc->execute();

            $localisationId = $conn->lastInsertId();

            // 2. Créer l'entreprise avec l'ID de localisation
            $stmtEnt = $conn->prepare('
            INSERT INTO Entreprise (
                Nom_Entreprise, 
                Description_Entreprise, 
                Email_Entreprise, 
                Telephone_Entreprise, 
                Effectif_Entreprise, 
                Id_Localisation
            )
            VALUES (
                :nom, 
                :description, 
                :email, 
                :telephone, 
                :effectif, 
                :localisationId
            )
        ');
            $stmtEnt->bindParam(':nom', $data['nom']);
            $stmtEnt->bindParam(':description', $data['description']);
            $stmtEnt->bindParam(':email', $data['email']);
            $stmtEnt->bindParam(':telephone', $data['telephone']);
            $stmtEnt->bindParam(':effectif', $data['effectif']);
            $stmtEnt->bindParam(':localisationId', $localisationId);
            $stmtEnt->execute();

            $enterpriseId = $conn->lastInsertId();

            // 3. Traiter la chaîne CSV des secteurs
            if (!empty($secteursCsv)) {
                // Découper la chaîne CSV en tableau de noms de secteurs
                $secteurNames = array_map('trim', explode(',', $secteursCsv));

                // Préparer les requêtes
                $stmtSectorFind = $conn->prepare('SELECT Id_Secteur FROM Secteur WHERE Nom_Secteur = :sectorName');
                $stmtSectorCreate = $conn->prepare('INSERT INTO Secteur (Nom_Secteur) VALUES (:sectorName)');
                $stmtFournir = $conn->prepare('INSERT INTO Fournir (Id_Entreprise, Id_Secteur) VALUES (:enterpriseId, :sectorId)');

                foreach ($secteurNames as $sectorName) {
                    if (empty($sectorName)) continue;

                    // Recherche du secteur par son nom
                    $stmtSectorFind->bindParam(':sectorName', $sectorName);
                    $stmtSectorFind->execute();
                    $existingSector = $stmtSectorFind->fetch(PDO::FETCH_ASSOC);

                    if ($existingSector) {
                        // Si le secteur existe, récupérer son ID
                        $sectorId = $existingSector['Id_Secteur'];
                    } else {
                        // Sinon, créer le secteur et récupérer son ID
                        $stmtSectorCreate->bindParam(':sectorName', $sectorName);
                        $stmtSectorCreate->execute();
                        $sectorId = $conn->lastInsertId();
                    }

                    // Associer le secteur à l'entreprise
                    $stmtFournir->bindParam(':enterpriseId', $enterpriseId);
                    $stmtFournir->bindParam(':sectorId', $sectorId);
                    $stmtFournir->execute();
                }
            }

            // Valider la transaction
            $conn->commit();

            return $enterpriseId;
        } catch (\Exception $e) {
            // En cas d'erreur, annuler la transaction
            $conn->rollBack();
            error_log('Erreur lors de la création de l\'entreprise: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Met à jour une entreprise existante avec sa localisation et ses secteurs
     *
     * @param int $enterpriseId ID de l'entreprise à mettre à jour
     * @param array $data Données de l'entreprise (nom, description, email, etc.)
     * @param string $secteursCsv Liste des secteurs au format CSV
     * @return bool Succès de l'opération
     */
    public function updateEnterprise($enterpriseId, $data, $secteursCsv = '') {
        $conn = $this->db->connect();

        try {
            // Démarrer une transaction
            $conn->beginTransaction();

            // 1. Récupérer l'ID de localisation associé à l'entreprise
            $stmt = $conn->prepare('SELECT Id_Localisation FROM Entreprise WHERE Id_Entreprise = :enterpriseId');
            $stmt->bindParam(':enterpriseId', $enterpriseId);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$result) {
                throw new \Exception("Entreprise non trouvée");
            }

            $localisationId = $result['Id_Localisation'];

            // 2. Mettre à jour la localisation
            $stmtLoc = $conn->prepare('
            UPDATE Localisation 
            SET Ville_Localisation = :ville,
                Code_Postal_Localisation = :codePostal,
                Adresse_Localisation = :adresse
            WHERE Id_Localisation = :localisationId
        ');
            $stmtLoc->bindParam(':ville', $data['ville']);
            $stmtLoc->bindParam(':codePostal', $data['codePostal']);
            $stmtLoc->bindParam(':adresse', $data['adresse']);
            $stmtLoc->bindParam(':localisationId', $localisationId);
            $stmtLoc->execute();

            // 3. Mettre à jour l'entreprise
            $stmtEnt = $conn->prepare('
            UPDATE Entreprise 
            SET Nom_Entreprise = :nom,
                Description_Entreprise = :description,
                Email_Entreprise = :email,
                Telephone_Entreprise = :telephone,
                Effectif_Entreprise = :effectif
            WHERE Id_Entreprise = :enterpriseId
        ');
            $stmtEnt->bindParam(':nom', $data['nom']);
            $stmtEnt->bindParam(':description', $data['description']);
            $stmtEnt->bindParam(':email', $data['email']);
            $stmtEnt->bindParam(':telephone', $data['telephone']);
            $stmtEnt->bindParam(':effectif', $data['effectif']);
            $stmtEnt->bindParam(':enterpriseId', $enterpriseId);
            $stmtEnt->execute();

            // 4. Supprimer toutes les associations de secteurs existantes
            $stmtDeleteSectors = $conn->prepare('
            DELETE FROM Fournir 
            WHERE Id_Entreprise = :enterpriseId
        ');
            $stmtDeleteSectors->bindParam(':enterpriseId', $enterpriseId);
            $stmtDeleteSectors->execute();

            // 5. Traiter et ajouter les nouveaux secteurs
            if (!empty($secteursCsv)) {
                // Normaliser et découper la chaîne CSV
                $secteurNames = array_map(function($name) {
                    return trim($name);
                }, explode(',', $secteursCsv));
                $secteurNames = array_filter($secteurNames, function($name) {
                    return !empty($name);
                });

                // Limiter le nombre de secteurs (sécurité et performance)
                if (count($secteurNames) > 15) {
                    $secteurNames = array_slice($secteurNames, 0, 15);
                }

                // Préparation des requêtes
                $stmtSectorFind = $conn->prepare('SELECT Id_Secteur FROM Secteur WHERE LOWER(Nom_Secteur) = LOWER(:sectorName)');
                $stmtSectorCreate = $conn->prepare('INSERT INTO Secteur (Nom_Secteur) VALUES (:sectorName)');
                $stmtFournir = $conn->prepare('INSERT INTO Fournir (Id_Entreprise, Id_Secteur) VALUES (:enterpriseId, :sectorId)');

                // Traitement de chaque secteur
                foreach ($secteurNames as $sectorName) {
                    // Recherche du secteur par son nom (insensible à la casse)
                    $stmtSectorFind->bindParam(':sectorName', $sectorName);
                    $stmtSectorFind->execute();
                    $existingSector = $stmtSectorFind->fetch(PDO::FETCH_ASSOC);

                    if ($existingSector) {
                        // Si le secteur existe, récupérer son ID
                        $sectorId = $existingSector['Id_Secteur'];
                    } else {
                        // Sinon, créer le secteur et récupérer son ID
                        // Normaliser la casse (première lettre majuscule)
                        $normalizedName = ucfirst(strtolower($sectorName));
                        $stmtSectorCreate->bindParam(':sectorName', $normalizedName);
                        $stmtSectorCreate->execute();
                        $sectorId = $conn->lastInsertId();
                    }

                    // Associer le secteur à l'entreprise
                    $stmtFournir->bindParam(':enterpriseId', $enterpriseId);
                    $stmtFournir->bindParam(':sectorId', $sectorId);
                    $stmtFournir->execute();
                }
            }

            // Valider la transaction
            $conn->commit();

            return true;
        } catch (\Exception $e) {
            // En cas d'erreur, annuler la transaction
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            error_log('Erreur lors de la mise à jour de l\'entreprise: ' . $e->getMessage());
            return false;
        }
    }
}