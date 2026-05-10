<?php
namespace App\Models;

use PDO;

class Creneau extends Model
{
    public static function create($date_heure): bool
    {
        $db = self::getDB();
        $sql = "INSERT INTO creneau (date_heure, disponible) VALUES (?, 1)";
        $stmt = $db->prepare($sql);
        return $stmt->execute([$date_heure]);
    }

    /**
     * Récupère uniquement les créneaux libres pour la planification
     */
    public static function getAvailable(): array
    {
        $db = self::getDB();
        return $db->query("SELECT * FROM creneau WHERE disponible = 1 ORDER BY date_heure ASC")
                  ->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>