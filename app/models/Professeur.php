<?php
namespace App\Models;

class Professeur extends Model
{
    public static function create(array $data): bool
    {
        $db = self::getDB();
        $sql = "INSERT INTO Professeur (nom, prenom, specialite) 
                VALUES (:nom, :prenom, :specialite)";
        
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            ':nom'        => $data['nom'],
            ':prenom'     => $data['prenom'],
            ':specialite' => $data['specialite'] ?? null
        ]);
    }
}
?>