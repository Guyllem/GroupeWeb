<?php
namespace App\Utils;

class FileUploadUtil {
    /**
     * Types de fichiers autorisés
     */
    private const ALLOWED_MIME_TYPES = [
        'application/pdf'
    ];

    /**
     * Taille maximale de fichier (2Mo en octets)
     */
    private const MAX_FILE_SIZE = 2097152; // 2 * 1024 * 1024

    /**
     * Chemin de base pour l'upload (sera complété avec la config du .env)
     */
    private $uploadBasePath;

    /**
     * Erreurs d'upload
     */
    private $errors = [];

    /**
     * Fichiers temporairement uploadés à nettoyer en cas d'erreur
     */
    private $uploadedFiles = [];

    /**
     * Labels des types de fichier pour les messages d'erreur
     */
    private $typeLabels = [
        'CV' => 'Curriculum Vitae',
        'LM' => 'Lettre de motivation',
        'Autre' => 'Document'
    ];

    /**
     * Constructeur
     */
    public function __construct() {
        // Récupérer le chemin d'upload depuis le .env
        $this->uploadBasePath = $_ENV['UPLOAD_PATH'] ?? 'uploads';

        // S'assurer que le chemin est relatif au répertoire racine
        if (strpos($this->uploadBasePath, '/') === 0) {
            $this->uploadBasePath = substr($this->uploadBasePath, 1);
        }

        // Créer le répertoire s'il n'existe pas
        $fullPath = dirname(__DIR__, 2) . '/' . $this->uploadBasePath;
        if (!file_exists($fullPath)) {
            mkdir($fullPath, 0755, true);
        }
    }

    /**
     * Valide un fichier uploadé
     *
     * @param array $file Élément de $_FILES
     * @param string $type Type attendu ('CV', 'LM', 'Autre')
     * @return bool True si le fichier est valide
     */
    public function validate($file, $type) {
        // Récupérer le label compréhensible pour ce type de fichier
        $fileLabel = $this->typeLabels[$type] ?? $type;

        // Vérifier si le fichier a été correctement uploadé
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->errors[$type] = "{$fileLabel}: " . $this->getUploadErrorMessage($file['error']);
            return false;
        }

        // Vérifier l'extension directement (méthode plus simple et fiable)
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($extension !== 'pdf') {
            $this->errors[$type] = "{$fileLabel}: Le fichier doit être au format PDF. Extension détectée: $extension";
            return false;
        }

        // Vérification supplémentaire: signature de fichier PDF (les 4 premiers octets devraient être %PDF)
        $handle = fopen($file['tmp_name'], 'r');
        if ($handle) {
            $header = fread($handle, 4);
            fclose($handle);
            if ($header !== '%PDF') {
                $this->errors[$type] = "{$fileLabel}: Le contenu du fichier ne semble pas être un PDF valide.";
                return false;
            }
        }

        // Vérifier la taille
        if ($file['size'] > self::MAX_FILE_SIZE) {
            $this->errors[$type] = "{$fileLabel}: La taille du fichier ne doit pas dépasser 2 Mo.";
            return false;
        }

        return true;
    }

    /**
     * Upload un fichier
     *
     * @param array $file Élément de $_FILES
     * @param string $type Type de fichier ('CV', 'LM', 'Autre')
     * @return array|null Informations sur le fichier uploadé ou null si erreur
     */
    public function upload($file, $type) {
        if (!$this->validate($file, $type)) {
            return null;
        }

        // Générer un nom de fichier unique
        $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $uniqueName = uniqid('file_', true) . '_' . time() . '.' . $fileExtension;

        // Déterminer le sous-répertoire en fonction du type
        $subDirectory = strtolower($type);
        $fullDirectory = dirname(__DIR__, 2) . '/' . $this->uploadBasePath . '/' . $subDirectory;

        // Créer le sous-répertoire si nécessaire
        if (!file_exists($fullDirectory)) {
            mkdir($fullDirectory, 0755, true);
        }

        $uploadPath = $fullDirectory . '/' . $uniqueName;
        $relativePath = $this->uploadBasePath . '/' . $subDirectory . '/' . $uniqueName;

        // Déplacer le fichier
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            // Enregistrer ce fichier pour pouvoir le supprimer si nécessaire
            $this->uploadedFiles[] = $uploadPath;

            return [
                'type' => $type,
                'original_name' => $file['name'],
                'path' => $relativePath,
                'size' => $file['size']
            ];
        } else {
            $fileLabel = $this->typeLabels[$type] ?? $type;
            $this->errors[$type] = "{$fileLabel}: Erreur lors de l'upload du fichier.";
            return null;
        }
    }

    /**
     * Nettoie tous les fichiers uploadés temporairement
     */
    public function cleanUploadedFiles() {
        foreach ($this->uploadedFiles as $filePath) {
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
        // Réinitialiser la liste des fichiers
        $this->uploadedFiles = [];
    }

    /**
     * Récupère les erreurs d'upload
     *
     * @return array Tableau des erreurs par type de fichier
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Traduit le code d'erreur d'upload en message
     *
     * @param int $errorCode Code d'erreur PHP
     * @return string Message d'erreur
     */
    private function getUploadErrorMessage($errorCode) {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return "Le fichier dépasse la taille maximale autorisée.";
            case UPLOAD_ERR_PARTIAL:
                return "Le fichier n'a été que partiellement uploadé.";
            case UPLOAD_ERR_NO_FILE:
                return "Aucun fichier n'a été uploadé.";
            case UPLOAD_ERR_NO_TMP_DIR:
                return "Le dossier temporaire est manquant.";
            case UPLOAD_ERR_CANT_WRITE:
                return "Impossible d'écrire le fichier sur le disque.";
            case UPLOAD_ERR_EXTENSION:
                return "Une extension PHP a arrêté l'upload.";
            default:
                return "Erreur inconnue lors de l'upload.";
        }
    }
}