<?php
namespace App\Utils;

use PDO;

class ConstraintChecker
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Lance toutes les vérifications et retourne la liste des anomalies.
     * Retourne [] si tout est correct.
     */
    public function verifierTout(): array
    {
        return array_merge(
            $this->verifierChevauchementSalles(),     // contrainte 1
            $this->verifierReposProfs(),               // contrainte 2
            $this->verifierDoubleAffectationProf(),    // contrainte 3
            $this->verifierMinimumProfsInfo(),         // contrainte 4
            $this->verifierPFEAnglaisSansProf()        // contrainte 5
        );
    }

    // ================================================================
    //  CONTRAINTE 1 — Chevauchement de salles
    //  Même salle assignée à 2 soutenances sur le même créneau
    // ================================================================
    private function verifierChevauchementSalles(): array
    {
        $sql = 'SELECT s1.id_stnc AS stnc1,
                       s2.id_stnc AS stnc2,
                       s1.num_salle,
                       c.heure_debut,
                       c.heure_fin,
                       c.date_cren
                FROM Soutenance s1
                JOIN Soutenance s2
                    ON s1.num_salle = s2.num_salle
                    AND s1.id_cren  = s2.id_cren
                    AND s1.id_stnc  < s2.id_stnc   -- éviter doublons
                JOIN Creneau c ON s1.id_cren = c.id_cren';

        $rows = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $anomalies = [];
        foreach ($rows as $row) {
            $anomalies[] = [
                'type'    => 'chevauchement_salle',
                'message' => "Salle {$row['num_salle']} assignée à 2 soutenances "
                           . "le {$row['date_cren']} de {$row['heure_debut']} à {$row['heure_fin']}",
                'data'    => $row,
            ];
        }
        return $anomalies;
    }

    // ================================================================
    //  CONTRAINTE 2 — Repos insuffisant (moins d'1h entre 2 soutenances)
    //  Un prof dans créneau 09-10 ne peut pas être dans 10-11
    // ================================================================
    private function verifierReposProfs(): array
    {
        $sql = 'SELECT p1.id_prof,
                       pr.nom, pr.prenom,
                       c1.date_cren,
                       c1.heure_debut AS debut1, c1.heure_fin AS fin1,
                       c2.heure_debut AS debut2, c2.heure_fin AS fin2,
                       s1.id_stnc AS stnc1,
                       s2.id_stnc AS stnc2
                FROM Participer p1
                JOIN Participer p2
                    ON p1.id_prof = p2.id_prof
                    AND p1.id_stnc < p2.id_stnc   -- éviter doublons
                JOIN Soutenance s1 ON p1.id_stnc = s1.id_stnc
                JOIN Soutenance s2 ON p2.id_stnc = s2.id_stnc
                JOIN Creneau c1    ON s1.id_cren  = c1.id_cren
                JOIN Creneau c2    ON s2.id_cren  = c2.id_cren
                JOIN Professeur pr ON p1.id_prof  = pr.id_prof
                WHERE c1.date_cren = c2.date_cren   -- même jour
                  AND (
                      c1.heure_fin  = c2.heure_debut  -- soutenance 2 commence quand 1 finit
                      OR
                      c2.heure_fin  = c1.heure_debut  -- inverse
                  )';

        $rows      = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $anomalies = [];

        foreach ($rows as $row) {
            $anomalies[] = [
                'type'    => 'repos_insuffisant',
                'message' => "Prof {$row['nom']} {$row['prenom']} : "
                           . "pas de repos entre {$row['fin1']} et {$row['debut2']} "
                           . "le {$row['date_cren']}",
                'data'    => $row,
            ];
        }
        return $anomalies;
    }

    // ================================================================
    //  CONTRAINTE 3 — Double affectation au même horaire
    //  Un prof dans 2 jurys différents sur le même créneau
    // ================================================================
    private function verifierDoubleAffectationProf(): array
    {
        $sql = 'SELECT p1.id_prof,
                       pr.nom, pr.prenom,
                       s1.id_stnc AS stnc1,
                       s2.id_stnc AS stnc2,
                       c.heure_debut, c.heure_fin, c.date_cren
                FROM Participer p1
                JOIN Participer p2
                    ON p1.id_prof  = p2.id_prof
                    AND p1.id_stnc < p2.id_stnc
                JOIN Soutenance s1 ON p1.id_stnc = s1.id_stnc
                JOIN Soutenance s2 ON p2.id_stnc = s2.id_stnc
                JOIN Creneau c     ON s1.id_cren  = c.id_cren
                JOIN Professeur pr ON p1.id_prof  = pr.id_prof
                WHERE s1.id_cren = s2.id_cren   -- même créneau exact';

        $rows      = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $anomalies = [];

        foreach ($rows as $row) {
            $anomalies[] = [
                'type'    => 'double_affectation',
                'message' => "Prof {$row['nom']} {$row['prenom']} : "
                           . "affecté à 2 soutenances le {$row['date_cren']} "
                           . "à {$row['heure_debut']}",
                'data'    => $row,
            ];
        }
        return $anomalies;
    }

    // ================================================================
    //  CONTRAINTE 4 — Moins de 2 profs informatique dans un jury
    // ================================================================
    private function verifierMinimumProfsInfo(): array
    {
        $sql = "SELECT s.id_stnc,
                       e.nom, e.prenom, e.sujet_pfe,
                       COUNT(pa.id_prof) AS nb_profs_info
                FROM Soutenance s
                JOIN Etudiant e ON s.id_etud = e.id_etud
                JOIN Participer pa ON s.id_stnc = pa.id_stnc
                JOIN Professeur pr ON pa.id_prof = pr.id_prof
                WHERE LOWER(pr.specialite) LIKE '%informatique%'
                GROUP BY s.id_stnc, e.nom, e.prenom, e.sujet_pfe
                HAVING nb_profs_info < 2";

        $rows      = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $anomalies = [];

        foreach ($rows as $row) {
            $anomalies[] = [
                'type'    => 'jury_incomplet_info',
                'message' => "Soutenance #{$row['id_stnc']} ({$row['nom']} {$row['prenom']}) : "
                           . "seulement {$row['nb_profs_info']} prof(s) informatique dans le jury",
                'data'    => $row,
            ];
        }
        return $anomalies;
    }

    // ================================================================
    //  CONTRAINTE 5 — PFE anglais sans prof anglais dans le jury
    // ================================================================
    private function verifierPFEAnglaisSansProf(): array
    {
        $sql = "SELECT s.id_stnc, e.nom, e.prenom, e.langue_pfe
                FROM Soutenance s
                JOIN Etudiant e ON s.id_etud = e.id_etud
                WHERE LOWER(e.langue_pfe) = 'anglais'
                  AND s.id_stnc NOT IN (
                      SELECT pa.id_stnc
                      FROM Participer pa
                      JOIN Professeur pr ON pa.id_prof = pr.id_prof
                      WHERE LOWER(pr.specialite) LIKE '%anglais%'
                  )";

        $rows      = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $anomalies = [];

        foreach ($rows as $row) {
            $anomalies[] = [
                'type'    => 'pfe_anglais_sans_prof',
                'message' => "Soutenance #{$row['id_stnc']} ({$row['nom']} {$row['prenom']}) : "
                           . "PFE en anglais mais aucun prof d'anglais dans le jury",
                'data'    => $row,
            ];
        }
        return $anomalies;
    }
}