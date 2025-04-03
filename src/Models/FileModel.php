<?php
namespace App\Models;

use PDO;

class FileModel extends Model {
    public function __construct($db) {
        parent::__construct($db, 'Fichier', 'Id_Fichier');
    }

    /**
     * Crée une entrée fichier en base de données
     *
     * @param string $type Type de fichier ('CV', 'LM', 'Autre')
     * @param string $displayName Nom d'affichage du fichier
     * @param string $path Chemin de stockage relatif
     * @return int|null ID du fichier créé ou null si erreur
     */
    public function createFile($type, $displayName, $path) {
        try {
            $conn = $this->db->connect();

            // Préparation de la requête
            $query = "INSERT INTO Fichier (Type_Fichier, Nom_Affichage_Fichier, Chemin_Fichier) 
                      VALUES (:type, :displayName, :path)";

            $stmt = $conn->prepare($query);

            // Vérification du type (enum dans la base)
            if (!in_array($type, ['CV', 'LM', 'Autre'])) {
                $type = 'Autre';
            }

            // Binding des paramètres
            $stmt->bindParam(':type', $type);
            $stmt->bindParam(':displayName', $displayName);
            $stmt->bindParam(':path', $path);

            $stmt->execute();

            // Récupération de l'ID généré
            return $conn->lastInsertId();

        } catch (\Exception $e) {
            error_log('Erreur lors de la création du fichier : ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Récupère les fichiers associés à une candidature
     *
     * @param int $candidatureId ID de la candidature
     * @return array Liste des fichiers associés
     */
    public function getFilesByCandidature($candidatureId) {
        try {
            $conn = $this->db->connect();

            $query = "SELECT f.* 
                      FROM Fichier f
                      JOIN Contenir c ON f.Id_Fichier = c.Id_Fichier
                      WHERE c.Id_Candidature = :candidatureId";

            $stmt = $conn->prepare($query);
            $stmt->bindParam(':candidatureId', $candidatureId);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (\Exception $e) {
            error_log('Erreur lors de la récupération des fichiers : ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Supprime un fichier de la base de données et du disque
     *
     * @param int $fileId ID du fichier à supprimer
     * @return bool Succès de l'opération
     */
    public function deleteFile($fileId) {
        try {
            $conn = $this->db->connect();

            // D'abord récupérer le chemin du fichier
            $stmt = $conn->prepare("SELECT Chemin_Fichier FROM Fichier WHERE Id_Fichier = :fileId");
            $stmt->bindParam(':fileId', $fileId);
            $stmt->execute();

            $file = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$file) {
                return false;
            }

            // Supprimer le fichier physique
            $filePath = dirname(__DIR__, 2) . '/' . $file['Chemin_Fichier'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            // Supprimer les relations dans Contenir
            $stmt = $conn->prepare("DELETE FROM Contenir WHERE Id_Fichier = :fileId");
            $stmt->bindParam(':fileId', $fileId);
            $stmt->execute();

            // Supprimer l'entrée dans la table Fichier
            $stmt = $conn->prepare("DELETE FROM Fichier WHERE Id_Fichier = :fileId");
            $stmt->bindParam(':fileId', $fileId);

            return $stmt->execute();

        } catch (\Exception $e) {
            error_log('Erreur lors de la suppression du fichier : ' . $e->getMessage());
            return false;
        }
    }
}