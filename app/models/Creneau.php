<?php
namespace App\Models;

use PDO;

class Creneau extends Model
{
    public static function create(array $data): bool
    {
        $db = self::getDB();
        $sql = "INSERT INTO Creneau (date_cren, heure_debut, heure_fin, id_session) 
                VALUES (:date_cren, :h_debut, :h_fin, :id_session)";
        
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            ':date_cren'  => $data['date_cren'],
            ':h_debut'    => $data['heure_debut'],
            ':h_fin'      => $data['heure_fin'],
            ':id_session' => $data['id_session']
        ]);
    }

    public static function getAvailable(): array
    {
        $db = self::getDB();
        // Un créneau est disponible s'il n'est pas déjà dans la table Soutenance
        $sql = "SELECT c.* FROM Creneau c 
                LEFT JOIN Soutenance s ON c.id_cren = s.id_cren 
                WHERE s.id_cren IS NULL 
                ORDER BY c.date_cren, c.heure_debut ASC";
        
        return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>