<?php
namespace App\Models;

use PDO;

class Soutenance extends Model
{
    /**
     * Planifie une soutenance en liant un étudiant, un jury et un créneau
     */
    public static function schedule(array $data): bool
    {
        $db = self::getDB();
        $sql = "INSERT INTO soutenance (id_etudiant, id_creneau, salle, titre_pfe) 
                VALUES (:id_etudiant, :id_creneau, :salle, :titre)";
        
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            ':id_etudiant' => $data['id_etudiant'],
            ':id_creneau'  => $data['id_creneau'],
            ':salle'       => $data['salle'],
            ':titre'       => $data['titre_pfe']
        ]);
    }

    public static function getFullPlanning(): array
    {
        $db = self::getDB();
        $sql = "SELECT s.*, e.nom as etudiant_nom, c.date_heure 
                FROM soutenance s
                JOIN etudiant e ON s.id_etudiant = e.id
                JOIN creneau c ON s.id_creneau = c.id";
        return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>