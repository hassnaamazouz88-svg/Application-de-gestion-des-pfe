<?php
namespace App\Models;

use PDO;

class Etudiant extends Model
{
    public static function create(array $data): bool
    {
        $db = self::getDB();
        $sql = "INSERT INTO Etudiant (nom, prenom, email, filiere, sujet_pfe, langue_pfe, id_prof) 
                VALUES (:nom, :prenom, :email, :filiere, :sujet_pfe, :langue_pfe, :id_prof)";
        
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            ':nom'        => $data['nom'],
            ':prenom'     => $data['prenom'],
            ':email'      => $data['email'],
            ':filiere'    => $data['filiere'] ?? null,
            ':sujet_pfe'  => $data['sujet_pfe'] ?? null,
            ':langue_pfe' => $data['langue_pfe'] ?? null,
            ':id_prof'    => $data['id_prof'] ?? null // L'encadrant optionnel au départ
        ]);
    }
}

?>