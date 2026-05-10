<?php
namespace App\Services;

use App\Models\Etudiant;
use App\Models\Professeur;
use PDO;

class AffectationSvc
{
    
    public function affecter(): void{

        $etudiants = Etudiant::getAll();

        foreach($etudiants as $etud){
            $prof = $this->trouverProfMoinsCharge();

            $this->affecterEtudiant($etud['id_etud'], $prof['id_prof']);
        }
    }

    private function trouverProfMoinsCharge(){
        $db = Professeur :: getDB();
        $sql = 'SELECT p.id_prof, COUNT(e.id_etud) AS NB_etud
                FROM professeur p
                LEFT JOIN etudiant e 
                ON p.id_prof = e.id_prof

                GROUP BY p.id_prof 
                ORDER BY NB_etud ASC 
                LIMIT 1';

                return $db->query($sql)->fetch(PDO::FETCH_ASSOC);
    }

    private function affecterEtudiant($id_etud, $id_prof): void{
        $db = Professeur :: getDB();
        $sql = 'UPDATE etudiant 
                SET id_prof = :id_prof
                WHERE id_etud = :id_etud';

       $stmt = $db->prepare($sql);
       $stmt->execute([
        ':id_prof' => $id_prof,
        ':id_etud' => $id_etud
       ]);
    }

}


?>