<?php
namespace App\Models;

use PDO;

class PromotionModel extends Model {
    public function __construct($db) {
        parent::__construct($db, 'Promotion', 'Id_Promotion');
    }

    /**
     * Obtient toutes les promotions
     *
     * @return array Liste des promotions
     */
    public function getAllPromotions() {
        $query = '
            SELECT 
                Id_Promotion, 
                Nom_Promotion, 
                Specialite_Promotion, 
                Niveau_Promotion,
                Id_Campus
            FROM Promotion
            ORDER BY Nom_Promotion ASC
        ';

        $conn = $this->db->connect();
        $stmt = $conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtient les promotions supervisées par un pilote
     *
     * @param int $pilotId ID du pilote
     * @return array Liste des promotions
     */
    public function getSupervisedPromotions($pilotId) {
        $query = '
        SELECT 
            p.Id_Promotion, 
            p.Nom_Promotion, 
            p.Specialite_Promotion,
            p.Statut_Promotion,
            p.Niveau_Promotion,
            c.Nom_Campus,
            c.Id_Campus
        FROM Superviser s
        JOIN Promotion p ON s.Id_Promotion = p.Id_Promotion
        LEFT JOIN Campus c ON p.Id_Campus = c.Id_Campus
        WHERE s.Id_Pilote = :pilotId
        ORDER BY p.Nom_Promotion ASC
    ';

        $conn = $this->db->connect();
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':pilotId', $pilotId);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}


