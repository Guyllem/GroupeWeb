<?php
namespace App\Controllers;

use App\Models\StudentModel;
use PDO;

class SkillsController extends BaseController {

    public function __construct($twig, $db) {
        parent::__construct($twig, $db);
    }

    /**
     * Ajoute une compétence à l'étudiant connecté
     */
    public function addSkill($params) {
        $this->requireEtudiant();

        // Récupérer l'ID de la compétence
        $skillId = $params['id'] ?? null;

        if (!$skillId) {
            $this->addFlashMessage('error', 'ID compétence manquant');
            header('Location: /etudiant/mon_profil');
            return;
        }

        // Récupérer l'ID de l'étudiant
        $userId = $_SESSION['user_id'];
        $studentModel = new StudentModel($this->db);
        $studentId = $studentModel->getStudentIdFromUserId($userId);

        if (!$studentId) {
            $this->addFlashMessage('error', 'Étudiant non trouvé');
            header('Location: /etudiant/mon_profil');
            return;
        }

        // Vérifier si l'étudiant possède déjà cette compétence
        $conn = $this->db->connect();
        $stmt = $conn->prepare('
            SELECT COUNT(*) FROM Posseder 
            WHERE Id_Etudiant = :studentId AND Id_Competence = :skillId
        ');
        $stmt->bindParam(':studentId', $studentId);
        $stmt->bindParam(':skillId', $skillId);
        $stmt->execute();

        if ($stmt->fetchColumn() > 0) {
            // Compétence déjà associée
            $this->addFlashMessage('info', 'Cette compétence est déjà dans votre profil');
            header('Location: /etudiant/mon_profil');
            return;
        }

        // Ajouter la compétence
        $stmt = $conn->prepare('
            INSERT INTO Posseder (Id_Etudiant, Id_Competence)
            VALUES (:studentId, :skillId)
        ');
        $stmt->bindParam(':studentId', $studentId);
        $stmt->bindParam(':skillId', $skillId);

        if ($stmt->execute()) {
            $this->addFlashMessage('success', 'Compétence ajoutée avec succès');
        } else {
            $this->addFlashMessage('error', 'Erreur lors de l\'ajout de la compétence');
        }

        header('Location: /etudiant/mon_profil');
    }

    /**
     * Supprime une compétence de l'étudiant connecté
     */
    public function deleteSkill($params) {
        $this->requireEtudiant();

        // Récupérer l'ID de la compétence
        $skillId = $params['id'] ?? null;

        if (!$skillId) {
            $this->addFlashMessage('error', 'ID compétence manquant');
            header('Location: /etudiant/mon_profil');
            return;
        }

        // Récupérer l'ID de l'étudiant
        $userId = $_SESSION['user_id'];
        $studentModel = new StudentModel($this->db);
        $studentId = $studentModel->getStudentIdFromUserId($userId);

        if (!$studentId) {
            $this->addFlashMessage('error', 'Étudiant non trouvé');
            header('Location: /etudiant/mon_profil');
            return;
        }

        // Supprimer la compétence
        $conn = $this->db->connect();
        $stmt = $conn->prepare('
            DELETE FROM Posseder 
            WHERE Id_Etudiant = :studentId AND Id_Competence = :skillId
        ');
        $stmt->bindParam(':studentId', $studentId);
        $stmt->bindParam(':skillId', $skillId);

        if ($stmt->execute()) {
            $this->addFlashMessage('success', 'Compétence supprimée avec succès');
        } else {
            $this->addFlashMessage('error', 'Erreur lors de la suppression de la compétence');
        }

        header('Location: /etudiant/mon_profil');
    }
}