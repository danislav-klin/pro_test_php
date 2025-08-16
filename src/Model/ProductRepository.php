<?php

namespace App\Model;

use App\Core\Database;
use PDO;

class ProductRepository
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function findAll($filters = [])
    {
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id";
        
        if (!empty($filters['name'])) {
            $sql .= " WHERE p.name LIKE '%" . $filters['name'] . "%'";
        }
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function findAllCategories()
    {
        $stmt = $this->pdo->query("SELECT * FROM categories ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function findById($id)
    {
        $stmt = $this->pdo->prepare("
        SELECT p.*, c.name as category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function deleteById($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM products WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function update($id, $data) 
    {
        $stmt = $this->pdo->prepare("
        UPDATE products 
        SET name = :name, description = :description, price = :price, category_id = :category_id
        WHERE id = :id");

        return $stmt->execute([
            ':id' => $id,
            ':name' => $data['name'],
            ':description' => $data['description'],
            ':price' => $data['price'],
            ':category_id' => $data['category_id']
        ]);
    }

    public function create($data) 
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO products (name, description, price, category_id)
            VALUES (:name, :description, :price, :category_id)
        ");
        $stmt->execute([
            ':name' => $data['name'],
            ':description' => $data['description'],
            ':price' => $data['price'],
            ':category_id' => $data['category_id']
        ]);
        

    }
    

}