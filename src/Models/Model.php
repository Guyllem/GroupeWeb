<?php
namespace App\Models;

use PDO;

class Model {
    protected $db;
    protected $tableName;
    protected $primaryKey;
    
    public function __construct($db, $tableName = null, $primaryKey = 'id') {
        $this->db = $db;
        $this->tableName = $tableName;
        $this->primaryKey = $primaryKey;
    }
    
    /**
     * Obtient tous les enregistrements d'une table
     * 
     * @param string $orderBy Colonne de tri
     * @param string $direction Direction du tri (ASC ou DESC)
     * @param int $limit Nombre maximum d'enregistrements à retourner
     * @param int $offset Position de départ
     * @return array
     */
    public function getAll($orderBy = null, $direction = 'ASC', $limit = null, $offset = null) {
        $query = "SELECT * FROM {$this->tableName}";
        
        if ($orderBy) {
            $query .= " ORDER BY {$orderBy} {$direction}";
        }
        
        if ($limit) {
            $query .= " LIMIT {$limit}";
            
            if ($offset) {
                $query .= " OFFSET {$offset}";
            }
        }
        
        $conn = $this->db->connect();
        $stmt = $conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtient un enregistrement par son ID
     * 
     * @param int $id ID de l'enregistrement
     * @return array|null
     */
    public function getById($id) {
        $conn = $this->db->connect();
        $stmt = $conn->prepare("SELECT * FROM {$this->tableName} WHERE {$this->primaryKey} = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Crée un nouvel enregistrement
     * 
     * @param array $data Données à insérer
     * @return int ID du nouvel enregistrement
     */
    public function create($data) {
        $fields = array_keys($data);
        $placeholders = array_map(function($field) {
            return ":{$field}";
        }, $fields);
        
        $query = "INSERT INTO {$this->tableName} (" . implode(', ', $fields) . ") 
                 VALUES (" . implode(', ', $placeholders) . ")";
        
        $conn = $this->db->connect();
        $stmt = $conn->prepare($query);
        
        foreach ($data as $field => $value) {
            $stmt->bindValue(":{$field}", $value);
        }
        
        $stmt->execute();
        
        return $conn->lastInsertId();
    }
    
    /**
     * Met à jour un enregistrement
     * 
     * @param int $id ID de l'enregistrement
     * @param array $data Données à mettre à jour
     * @return bool
     */
    public function update($id, $data) {
        $fields = array_map(function($field) {
            return "{$field} = :{$field}";
        }, array_keys($data));
        
        $query = "UPDATE {$this->tableName} SET " . implode(', ', $fields) . " 
                 WHERE {$this->primaryKey} = :id";
        
        $conn = $this->db->connect();
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        foreach ($data as $field => $value) {
            $stmt->bindValue(":{$field}", $value);
        }
        
        return $stmt->execute();
    }
    
    /**
     * Supprime un enregistrement
     * 
     * @param int $id ID de l'enregistrement
     * @return bool
     */
    public function delete($id) {
        $conn = $this->db->connect();
        $stmt = $conn->prepare("DELETE FROM {$this->tableName} WHERE {$this->primaryKey} = :id");
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
    
    /**
     * Recherche des enregistrements selon certains critères
     * 
     * @param array $criteria Critères de recherche (ex: ['nom' => 'Dupont'])
     * @param string $operator Opérateur logique entre les critères (AND ou OR)
     * @return array
     */
    public function findBy($criteria, $operator = 'AND') {
        $fields = array_map(function($field) {
            return "{$field} = :{$field}";
        }, array_keys($criteria));
        
        $query = "SELECT * FROM {$this->tableName} WHERE " . implode(" {$operator} ", $fields);
        
        $conn = $this->db->connect();
        $stmt = $conn->prepare($query);
        
        foreach ($criteria as $field => $value) {
            $stmt->bindValue(":{$field}", $value);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Exécute une requête SQL personnalisée
     * 
     * @param string $query Requête SQL
     * @param array $params Paramètres de la requête
     * @param bool $fetchAll Retourner tous les résultats ou seulement le premier
     * @return mixed
     */
    public function executeQuery($query, $params = [], $fetchAll = true) {
        $conn = $this->db->connect();
        $stmt = $conn->prepare($query);
        
        foreach ($params as $param => $value) {
            $stmt->bindValue(":{$param}", $value);
        }
        
        $stmt->execute();
        
        if ($fetchAll) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}