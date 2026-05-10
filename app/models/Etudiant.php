<?php
namespace App\Models;

use PDO;

class Etudiant extends Model
{
    /**
     * Crée un nouvel étudiant dans la base de données
     */
    public static function create(array $data): bool
    {
        $db = self::getDB();
        $sql = "INSERT INTO etudiant (nom, prenom, email, filiere, sujet_pfe, langue_pfe) 
                VALUES (:nom, :prenom, :email, :filiere, :sujet_pfe, :langue_pfe)";
        
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            ':nom'        => $data['nom'],
            ':prenom'     => $data['prenom'],
            ':email'      => $data['email'],
            ':filiere'    => $data['filiere'],
            ':sujet_pfe'  => $data['sujet_pfe'],
            ':langue_pfe' => $data['langue_pfe']
        ]);
    }

    /**
     * Récupère tous les étudiants
     */
    public static function getAll(): array
    {
        $db = self::getDB();
        return $db->query("SELECT * FROM etudiant ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>