<?php
namespace App\Models;

use PDO;

class Soutenance extends Model
{
    public static function schedule(array $data): bool
    {
        $db = self::getDB();
        $sql = "INSERT INTO Soutenance (id_etud, num_salle, id_cren) 
                VALUES (:id_etud, :num_salle, :id_cren)";
        
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            ':id_etud'   => $data['id_etud'],
            ':num_salle' => $data['num_salle'],
            ':id_cren'   => $data['id_cren']
        ]);
    }
}

?>