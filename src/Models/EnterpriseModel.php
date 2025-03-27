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
                AVG(ev.Note_Evaluer) as rating
            FROM Entreprise e
            LEFT JOIN Evaluer ev ON e.Id_Entreprise = ev.Id_Entreprise
            GROUP BY e.Id_Entreprise
            ORDER BY rating DESC
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
    public function getEnterprisesByName($limit = 10, $offset = 0) {
        $query = '
            SELECT 
                e.Id_Entreprise, 
                e.Nom_Entreprise, 
                e.Description_Entreprise
            FROM Entreprise e
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
                AVG(ev.Note_Evaluer) as rating
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
     * Crée une nouvelle entreprise
     *
     * @param array $enterpriseData Données de l'entreprise
     * @param array $sectorIds IDs des secteurs
     * @return int|null ID de la nouvelle entreprise, null si erreur
     */
    public function createEnterprise($enterpriseData, $sectorIds = []) {
        $conn = $this->db->connect();

        try {
            $conn->beginTransaction();

            // Créer la localisation
            $stmt = $conn->prepare('
                INSERT INTO Localisation (
                    Ville_Localisation, 
                    Code_Postal_Localisation, 
                    Adresse_Localisation
                ) VALUES (
                    :ville, 
                    :codePostal, 
                    :adresse
                )
            ');

            $stmt->bindParam(':ville', $enterpriseData['ville']);
            $stmt->bindParam(':codePostal', $enterpriseData['codePostal']);
            $stmt->bindParam(':adresse', $enterpriseData['adresse']);
            $stmt->execute();

            $localisationId = $conn->lastInsertId();

            // Créer l'entreprise
            $stmt = $conn->prepare('
                INSERT INTO Entreprise (
                    Nom_Entreprise, 
                    Description_Entreprise, 
                    Email_Entreprise,
                    Telephone_Entreprise, 
                    Effectif_Entreprise,
                    Id_Localisation
                ) VALUES (
                    :nom, 
                    :description, 
                    :email,
                    :telephone, 
                    :effectif,
                    :idLocalisation
                )
            ');

            $stmt->bindParam(':nom', $enterpriseData['nom']);
            $stmt->bindParam(':description', $enterpriseData['description']);
            $stmt->bindParam(':email', $enterpriseData['email']);
            $stmt->bindParam(':telephone', $enterpriseData['telephone']);
            $stmt->bindParam(':effectif', $enterpriseData['effectif']);
            $stmt->bindParam(':idLocalisation', $localisationId);

            $stmt->execute();

            $enterpriseId = $conn->lastInsertId();

            // Ajouter les secteurs
            if (!empty($sectorIds)) {
                $insertSectorQuery = 'INSERT INTO Fournir (Id_Entreprise, Id_Secteur) VALUES ';
                $values = [];

                foreach ($sectorIds as $sectorId) {
                    $values[] = "({$enterpriseId}, {$sectorId})";
                }

                $insertSectorQuery .= implode(', ', $values);
                $conn->exec($insertSectorQuery);
            }

            $conn->commit();

            return $enterpriseId;
        } catch (\Exception $e) {
            $conn->rollBack();
            error_log($e->getMessage());
            return null;
        }
    }

    /**
     * Met à jour une entreprise existante
     *
     * @param int $enterpriseId ID de l'entreprise
     * @param array $enterpriseData Données de l'entreprise
     * @param array $sectorIds IDs des secteurs
     * @return bool Succès de l'opération
     */
    public function updateEnterprise($enterpriseId, $enterpriseData, $sectorIds = []) {
        $conn = $this->db->connect();

        try {
            $conn->beginTransaction();

            // Récupérer l'ID de localisation de l'entreprise
            $stmt = $conn->prepare('SELECT Id_Localisation FROM Entreprise WHERE Id_Entreprise = :enterpriseId');
            $stmt->bindParam(':enterpriseId', $enterpriseId);
            $stmt->execute();
            $localisationId = $stmt->fetchColumn();

            // Mettre à jour la localisation
            $stmt = $conn->prepare('
                UPDATE Localisation SET
                    Ville_Localisation = :ville,
                    Code_Postal_Localisation = :codePostal,
                    Adresse_Localisation = :adresse
                WHERE Id_Localisation = :localisationId
            ');

            $stmt->bindParam(':ville', $enterpriseData['ville']);
            $stmt->bindParam(':codePostal', $enterpriseData['codePostal']);
            $stmt->bindParam(':adresse', $enterpriseData['adresse']);
            $stmt->bindParam(':localisationId', $localisationId);
            $stmt->execute();

            // Mettre à jour l'entreprise
            $stmt = $conn->prepare('
                UPDATE Entreprise SET
                    Nom_Entreprise = :nom,
                    Description_Entreprise = :description,
                    Email_Entreprise = :email,
                    Telephone_Entreprise = :telephone,
                    Effectif_Entreprise = :effectif
                WHERE Id_Entreprise = :enterpriseId
            ');

            $stmt->bindParam(':nom', $enterpriseData['nom']);
            $stmt->bindParam(':description', $enterpriseData['description']);
            $stmt->bindParam(':email', $enterpriseData['email']);
            $stmt->bindParam(':telephone', $enterpriseData['telephone']);
            $stmt->bindParam(':effectif', $enterpriseData['effectif']);
            $stmt->bindParam(':enterpriseId', $enterpriseId);
            $stmt->execute();

            // Mettre à jour les secteurs
            // D'abord supprimer les anciens secteurs
            $stmt = $conn->prepare('DELETE FROM Fournir WHERE Id_Entreprise = :enterpriseId');
            $stmt->bindParam(':enterpriseId', $enterpriseId);
            $stmt->execute();

            // Ajouter les nouveaux secteurs
            if (!empty($sectorIds)) {
                $insertSectorQuery = 'INSERT INTO Fournir (Id_Entreprise, Id_Secteur) VALUES ';
                $values = [];

                foreach ($sectorIds as $sectorId) {
                    $values[] = "({$enterpriseId}, {$sectorId})";
                }

                $insertSectorQuery .= implode(', ', $values);
                $conn->exec($insertSectorQuery);
            }

            $conn->commit();

            return true;
        } catch (\Exception $e) {
            $conn->rollBack();
            error_log($e->getMessage());
            return false;
        }
    }
}