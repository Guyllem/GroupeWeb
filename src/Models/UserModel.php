<?php
namespace App\Models;

use PDO;
use App\Utils\SecurityUtil;

class UserModel extends Model {
    public function __construct($db) {
        parent::__construct($db, 'Utilisateur', 'Id_Utilisateur');
    }

    /**
     * Authentifie un utilisateur
     *
     * @param string $email Email de l'utilisateur
     * @param string $password Mot de passe
     * @return array|null Données de l'utilisateur si authentifié, null sinon
     */
    public function authenticate($email, $password) {
        $conn = $this->db->connect();
        $stmt = $conn->prepare('SELECT * FROM Utilisateur WHERE Email_Utilisateur = :email');
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Vérification sécurisée du mot de passe
        if ($user) {
            // Pour les mots de passe déjà hachés
            if (password_verify($password, $user['Password_Utilisateur'])) {
                // Rehachage si nécessaire (si un algorithme plus récent est disponible)
                $this->rehashIfNeeded($user['Id_Utilisateur'], $password, $user['Password_Utilisateur']);
                return $user;
            }

            // Pour la compatibilité avec les mots de passe non hachés existants
            // Cette partie devrait être supprimée après migration complète
            if ($user['Password_Utilisateur'] === $password) {
                // Mettre à jour le mot de passe avec un hachage
                $this->updatePasswordHash($user['Id_Utilisateur'], $password);
                return $user;
            }
        }

        return null;
    }

    /**
     * Rehache le mot de passe si nécessaire
     *
     * @param int $userId ID de l'utilisateur
     * @param string $password Mot de passe en clair
     * @param string $hashedPassword Mot de passe haché actuel
     */
    private function rehashIfNeeded($userId, $password, $hashedPassword) {
        if (password_needs_rehash($hashedPassword, PASSWORD_DEFAULT)) {
            $this->updatePasswordHash($userId, $password);
        }
    }

    /**
     * Met à jour le hachage du mot de passe d'un utilisateur
     *
     * @param int $userId ID de l'utilisateur
     * @param string $password Mot de passe en clair
     * @return bool
     */
    public function updatePasswordHash($userId, $password) {
        $hashedPassword = SecurityUtil::hashPassword($password);

        $conn = $this->db->connect();
        $stmt = $conn->prepare('
            UPDATE Utilisateur 
            SET Password_Utilisateur = :password 
            WHERE Id_Utilisateur = :userId
        ');
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':userId', $userId);

        return $stmt->execute();
    }

    /**
     * Détermine le type d'un utilisateur
     *
     * @param int $userId ID de l'utilisateur
     * @return string Type de l'utilisateur ('admin', 'pilote', 'etudiant' ou null)
     */
    public function getUserType($userId) {
        $conn = $this->db->connect();

        // Vérifier si l'utilisateur est un administrateur
        $stmt = $conn->prepare('SELECT * FROM Administrateur WHERE Id_Utilisateur = :userId');
        $stmt->bindParam(':userId', $userId);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return 'admin';
        }

        // Vérifier si l'utilisateur est un pilote
        $stmt = $conn->prepare('SELECT * FROM Pilote WHERE Id_Utilisateur = :userId');
        $stmt->bindParam(':userId', $userId);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return 'pilote';
        }

        // Vérifier si l'utilisateur est un étudiant
        $stmt = $conn->prepare('SELECT * FROM Etudiant WHERE Id_Utilisateur = :userId');
        $stmt->bindParam(':userId', $userId);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return 'etudiant';
        }

        return null;
    }

    /**
     * Crée un nouvel utilisateur
     *
     * @param string $email Email de l'utilisateur
     * @param string $password Mot de passe
     * @param string $nom Nom de l'utilisateur
     * @param string $prenom Prénom de l'utilisateur
     * @param string $type Type d'utilisateur ('admin', 'pilote' ou 'etudiant')
     * @return int|null ID du nouvel utilisateur, null si erreur
     */
    public function createUser($email, $password, $nom, $prenom, $type) {
        $conn = $this->db->connect();

        try {
            $conn->beginTransaction();

            // Hacher le mot de passe avec SecurityUtil
            $hashedPassword = SecurityUtil::hashPassword($password);

            // Créer l'utilisateur
            $stmt = $conn->prepare('
                INSERT INTO Utilisateur (Email_Utilisateur, Password_Utilisateur, Nom_Utilisateur, Prenom_Utilisateur)
                VALUES (:email, :password, :nom, :prenom)
            ');
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':prenom', $prenom);
            $stmt->execute();

            $userId = $conn->lastInsertId();

            // Créer l'entrée correspondante dans la table du type d'utilisateur
            switch ($type) {
                case 'admin':
                    $stmt = $conn->prepare('INSERT INTO Administrateur (Id_Utilisateur) VALUES (:userId)');
                    break;
                case 'pilote':
                    $stmt = $conn->prepare('INSERT INTO Pilote (Id_Utilisateur) VALUES (:userId)');
                    break;
                case 'etudiant':
                    $stmt = $conn->prepare('INSERT INTO Etudiant (Id_Utilisateur) VALUES (:userId)');
                    break;
                default:
                    throw new \Exception("Type d'utilisateur invalide");
            }

            $stmt->bindParam(':userId', $userId);
            $stmt->execute();

            $conn->commit();

            return $userId;
        } catch (\Exception $e) {
            $conn->rollBack();
            error_log($e->getMessage());
            return null;
        }
    }

    /**
     * Met à jour les informations d'un utilisateur
     *
     * @param int $userId ID de l'utilisateur
     * @param string $email Nouvel email (ou null pour ne pas changer)
     * @param string $password Nouveau mot de passe (ou null pour ne pas changer)
     * @param string $nom Nouveau nom (ou null pour ne pas changer)
     * @param string $prenom Nouveau prénom (ou null pour ne pas changer)
     * @return bool Succès de l'opération
     */
    public function updateUser($userId, $email = null, $password = null, $nom = null, $prenom = null) {
        $conn = $this->db->connect();

        try {
            $updates = [];
            $params = [':userId' => $userId];

            if ($email !== null) {
                $updates[] = 'Email_Utilisateur = :email';
                $params[':email'] = $email;
            }

            if ($password !== null) {
                $updates[] = 'Password_Utilisateur = :password';
                $params[':password'] = SecurityUtil::hashPassword($password);
            }

            if ($nom !== null) {
                $updates[] = 'Nom_Utilisateur = :nom';
                $params[':nom'] = $nom;
            }

            if ($prenom !== null) {
                $updates[] = 'Prenom_Utilisateur = :prenom';
                $params[':prenom'] = $prenom;
            }

            if (empty($updates)) {
                return true; // Rien à mettre à jour
            }

            $query = 'UPDATE Utilisateur SET ' . implode(', ', $updates) . ' WHERE Id_Utilisateur = :userId';

            $stmt = $conn->prepare($query);

            foreach ($params as $param => $value) {
                $stmt->bindValue($param, $value);
            }

            return $stmt->execute();
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Crée un utilisateur et un étudiant associé dans une transaction atomique garantie
     *
     * @param string $email Email de l'utilisateur
     * @param string $password Mot de passe en clair (sera haché)
     * @param string $nom Nom de l'utilisateur
     * @param string $prenom Prénom de l'utilisateur
     * @return array|null Tableau avec les IDs ou null en cas d'erreur
     */
    public function createStudentWithUser($email, $password, $nom, $prenom) {
        $conn = $this->db->connect();

        try {
            // Isolation explicite pour garantir la cohérence
            $conn->beginTransaction();

            // Hachage sécurisé du mot de passe
            $hashedPassword = \App\Utils\SecurityUtil::hashPassword($password);

            // 1. Insertion de l'utilisateur
            $stmt = $conn->prepare('
            INSERT INTO Utilisateur (
                Email_Utilisateur, 
                Password_Utilisateur, 
                Nom_Utilisateur, 
                Prenom_Utilisateur
            ) VALUES (
                :email, 
                :password, 
                :nom, 
                :prenom
            )
        ');
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':prenom', $prenom);
            $stmt->execute();

            $userId = $conn->lastInsertId();

            if (!$userId) {
                throw new \Exception("Échec de l'insertion de l'utilisateur");
            }

            // 2. Insertion de l'étudiant associé
            $stmt = $conn->prepare('INSERT INTO Etudiant (Id_Utilisateur) VALUES (:userId)');
            $stmt->bindParam(':userId', $userId);
            $stmt->execute();

            $studentId = $conn->lastInsertId();

            if (!$studentId) {
                throw new \Exception("Échec de l'insertion de l'étudiant");
            }

            // Validation de la transaction atomique
            $conn->commit();

            // Retour des identifiants générés
            return [
                'userId' => $userId,
                'studentId' => $studentId
            ];

        } catch (\Exception $e) {
            // Annulation de toutes les modifications en cas d'erreur
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }

            error_log("Transaction createStudentWithUser échouée: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Met à jour le numéro de téléphone d'un utilisateur
     *
     * @param int $userId ID de l'utilisateur
     * @param string $telephone Numéro de téléphone
     * @return bool Succès de l'opération
     */
    public function updateUserPhone($userId, $telephone) {
        if (empty($telephone)) {
            return true; // Rien à mettre à jour
        }

        try {
            $conn = $this->db->connect();
            $stmt = $conn->prepare('
            UPDATE Utilisateur 
            SET Telephone_Utilisateur = :telephone 
            WHERE Id_Utilisateur = :userId
        ');
            $stmt->bindParam(':telephone', $telephone);
            $stmt->bindParam(':userId', $userId);
            return $stmt->execute();
        } catch (\Exception $e) {
            error_log('Erreur lors de la mise à jour du téléphone: ' . $e->getMessage());
            return false;
        }
    }
}