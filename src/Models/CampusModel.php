<?php
namespace App\Models;

use PDO;

class CampusModel extends Model {
    public function __construct($db) {
        parent::__construct($db, 'Campus', 'Id_Campus');
    }

    /**
     * Obtient tous les campus
     *
     * @return array Liste des campus
     */
    public function getAllCampus() {
        $query = '
            SELECT 
                Id_Campus, 
                Nom_Campus
            FROM Campus
            ORDER BY Nom_Campus ASC
        ';

        $conn = $this->db->connect();
        $stmt = $conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
