<?php
namespace App\Models;

use PDO;

class Professeur extends Model
{
    public static function create(array $data): bool
    {
        $db = self::getDB();
        $sql = "INSERT INTO professeur (nom, prenom, email, specialite) 
                VALUES (:nom, :prenom, :email, :specialite)";
        
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            ':nom'         => $data['nom'],
            ':prenom'      => $data['prenom'],
            ':email'       => $data['email'],
            ':specialite' => $data['specialite']
        ]);
    }

    public static function getAll(): array
    {
        $db = self::getDB();
        return $db->query("SELECT * FROM professeur ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>