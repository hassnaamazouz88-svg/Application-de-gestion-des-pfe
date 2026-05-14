<?php

namespace App\Utils;

use PDO;

/**
 * ConstraintChecker.php
 * ─────────────────────
 * Classe utilitaire responsable de TOUTES les vérifications métier :
 *  1. Chevauchement de salles (même salle, même créneau)
 *  2. Double affectation d'un prof (même créneau, deux jurys différents)
 *  3. Repos insuffisant (moins d'1h entre deux soutenances d'un même prof)
 *  4. Minimum 2 profs informatique dans chaque jury
 *  5. PFE anglais sans prof d'anglais dans le jury
 *  6. Soutenance sans jury (aucun membre dans Participer)
 *  7. Étudiant sans encadrant (id_prof NULL)
 *
 * Retourne toujours un tableau structuré :
 *   [ 'errors' => [], 'warnings' => [], 'success' => [] ]
 */
class ConstraintChecker
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // ════════════════════════════════════════════════════════════════
    //  POINT D'ENTRÉE PRINCIPAL
    //  Lance toutes les vérifications et retourne le rapport complet
    // ════════════════════════════════════════════════════════════════

    /**
     * Lance toutes les vérifications.
     * @return array [ 'errors' => [], 'warnings' => [], 'success' => [] ]
     */
    public function verifierTout(): array
    {
        $errors   = [];
        $warnings = [];
        $success  = [];

        // ── ERREURS CRITIQUES ──────────────────────────────────────
        $chevauchements   = $this->verifierChevauchementSalles();
        $doubleAffect     = $this->verifierDoubleAffectationProf();
        $sansJury         = $this->verifierSoutenanceSansJury();
        $sansEncadrant    = $this->verifierEtudiantSansEncadrant();

        $errors = array_merge($chevauchements, $doubleAffect, $sansJury, $sansEncadrant);

        // ── AVERTISSEMENTS ─────────────────────────────────────────
        $repos      = $this->verifierReposProfs();
        $juryInfo   = $this->verifierMinimumProfsInfo();
        $langPFE    = $this->verifierPFEAnglaisSansProf();
        $equilibre  = $this->verifierEquilibreEncadrement();

        $warnings = array_merge($repos, $juryInfo, $langPFE, $equilibre);

        // ── SUCCÈS (contraintes OK) ────────────────────────────────
        if (empty($chevauchements)) {
            $success[] = [
                'type'    => 'ok_salle',
                'message' => 'Aucun chevauchement de salle détecté',
            ];
        }
        if (empty($doubleAffect)) {
            $success[] = [
                'type'    => 'ok_double',
                'message' => 'Aucune double affectation de professeur',
            ];
        }
        if (empty($repos)) {
            $success[] = [
                'type'    => 'ok_repos',
                'message' => 'Tous les professeurs ont un repos suffisant entre leurs soutenances',
            ];
        }
        if (empty($sansJury)) {
            $success[] = [
                'type'    => 'ok_jury',
                'message' => 'Toutes les soutenances ont un jury affecté',
            ];
        }
        if (empty($sansEncadrant)) {
            $success[] = [
                'type'    => 'ok_encadrant',
                'message' => 'Tous les étudiants ont un encadrant',
            ];
        }
        if (empty($juryInfo)) {
            $success[] = [
                'type'    => 'ok_jury_info',
                'message' => 'Tous les jurys ont au moins 2 professeurs informatique',
            ];
        }
        if (empty($langPFE)) {
            $success[] = [
                'type'    => 'ok_langue',
                'message' => 'Toutes les PFE en anglais ont un professeur de langue dans le jury',
            ];
        }

        return [
            'errors'   => $errors,
            'warnings' => $warnings,
            'success'  => $success,
        ];
    }

    // ════════════════════════════════════════════════════════════════
    //  CONTRAINTE 1 — Chevauchement de salles
    //  Deux soutenances dans la même salle au même créneau
    // ════════════════════════════════════════════════════════════════

    private function verifierChevauchementSalles(): array
    {
        $sql = 'SELECT s1.id_stnc  AS stnc1,
                       s2.id_stnc  AS stnc2,
                       s1.num_salle,
                       c.heure_debut,
                       c.heure_fin,
                       c.date_cren
                FROM   Soutenance s1
                JOIN   Soutenance s2
                    ON s1.num_salle = s2.num_salle
                   AND s1.id_cren   = s2.id_cren
                   AND s1.id_stnc   < s2.id_stnc
                JOIN   Creneau c ON s1.id_cren = c.id_cren';

        $rows      = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $anomalies = [];

        foreach ($rows as $row) {
            $anomalies[] = [
                'type'    => 'chevauchement_salle',
                'level'   => 'error',
                'message' => "Salle {$row['num_salle']} : deux soutenances (#"
                           . "{$row['stnc1']} et #{$row['stnc2']}) le {$row['date_cren']} "
                           . "de {$row['heure_debut']} à {$row['heure_fin']}",
                'data'    => $row,
            ];
        }

        return $anomalies;
    }

    // ════════════════════════════════════════════════════════════════
    //  CONTRAINTE 2 — Double affectation prof au même créneau
    //  Un prof dans deux jurys différents sur le même créneau exact
    // ════════════════════════════════════════════════════════════════

    private function verifierDoubleAffectationProf(): array
    {
        $sql = 'SELECT p1.id_prof,
                       pr.nom, pr.prenom,
                       s1.id_stnc  AS stnc1,
                       s2.id_stnc  AS stnc2,
                       c.heure_debut, c.heure_fin, c.date_cren
                FROM   Participer p1
                JOIN   Participer p2
                    ON p1.id_prof  = p2.id_prof
                   AND p1.id_stnc  < p2.id_stnc
                JOIN   Soutenance s1 ON p1.id_stnc = s1.id_stnc
                JOIN   Soutenance s2 ON p2.id_stnc = s2.id_stnc
                JOIN   Creneau    c  ON s1.id_cren  = c.id_cren
                JOIN   Professeur pr ON p1.id_prof  = pr.id_prof
                WHERE  s1.id_cren = s2.id_cren';

        $rows      = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $anomalies = [];

        foreach ($rows as $row) {
            $anomalies[] = [
                'type'    => 'double_affectation',
                'level'   => 'error',
                'message' => "Prof {$row['nom']} {$row['prenom']} affecté à 2 soutenances "
                           . "(#{$row['stnc1']} et #{$row['stnc2']}) le {$row['date_cren']} "
                           . "à {$row['heure_debut']}",
                'data'    => $row,
            ];
        }

        return $anomalies;
    }

    // ════════════════════════════════════════════════════════════════
    //  CONTRAINTE 3 — Repos insuffisant (< 1 h entre 2 soutenances)
    //  Détecte les créneaux consécutifs d'un même prof le même jour
    // ════════════════════════════════════════════════════════════════

    private function verifierReposProfs(): array
    {
        $sql = 'SELECT p1.id_prof,
                       pr.nom, pr.prenom,
                       c1.date_cren,
                       c1.heure_debut AS debut1, c1.heure_fin AS fin1,
                       c2.heure_debut AS debut2, c2.heure_fin AS fin2,
                       s1.id_stnc AS stnc1,
                       s2.id_stnc AS stnc2
                FROM   Participer p1
                JOIN   Participer p2
                    ON p1.id_prof  = p2.id_prof
                   AND p1.id_stnc  < p2.id_stnc
                JOIN   Soutenance s1 ON p1.id_stnc = s1.id_stnc
                JOIN   Soutenance s2 ON p2.id_stnc = s2.id_stnc
                JOIN   Creneau    c1 ON s1.id_cren  = c1.id_cren
                JOIN   Creneau    c2 ON s2.id_cren  = c2.id_cren
                JOIN   Professeur pr ON p1.id_prof  = pr.id_prof
                WHERE  c1.date_cren = c2.date_cren
                  AND  (
                        TIMESTAMPDIFF(MINUTE, c1.heure_fin, c2.heure_debut) BETWEEN 0 AND 59
                        OR
                        TIMESTAMPDIFF(MINUTE, c2.heure_fin, c1.heure_debut) BETWEEN 0 AND 59
                  )';

        $rows      = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $anomalies = [];

        foreach ($rows as $row) {
            // Calcul du repos réel en minutes
            $repos = abs(
                (strtotime($row['debut2']) - strtotime($row['fin1'])) / 60
            );

            $anomalies[] = [
                'type'    => 'repos_insuffisant',
                'level'   => 'warning',
                'message' => "Prof {$row['nom']} {$row['prenom']} : repos de seulement "
                           . "{$repos} min entre {$row['fin1']} et {$row['debut2']} "
                           . "le {$row['date_cren']}",
                'data'    => array_merge($row, ['repos_minutes' => $repos]),
            ];
        }

        return $anomalies;
    }

    // ════════════════════════════════════════════════════════════════
    //  CONTRAINTE 4 — Moins de 2 profs informatique dans un jury
    // ════════════════════════════════════════════════════════════════

    private function verifierMinimumProfsInfo(): array
    {
        $sql = "SELECT s.id_stnc,
                       CONCAT(e.nom,' ',e.prenom) AS etudiant,
                       e.sujet_pfe,
                       COUNT(pa.id_prof) AS nb_profs_info
                FROM   Soutenance  s
                JOIN   Etudiant    e  ON s.id_etud  = e.id_etud
                JOIN   Participer  pa ON s.id_stnc  = pa.id_stnc
                JOIN   Professeur  pr ON pa.id_prof = pr.id_prof
                WHERE  LOWER(pr.specialite) LIKE '%informatique%'
                GROUP  BY s.id_stnc, e.nom, e.prenom, e.sujet_pfe
                HAVING nb_profs_info < 2";

        $rows      = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $anomalies = [];

        foreach ($rows as $row) {
            $anomalies[] = [
                'type'    => 'jury_incomplet_info',
                'level'   => 'warning',
                'message' => "Soutenance #{$row['id_stnc']} ({$row['etudiant']}) : "
                           . "seulement {$row['nb_profs_info']} prof(s) informatique dans le jury "
                           . "(minimum requis : 2)",
                'data'    => $row,
            ];
        }

        return $anomalies;
    }

    // ════════════════════════════════════════════════════════════════
    //  CONTRAINTE 5 — PFE anglais sans prof de langue dans le jury
    // ════════════════════════════════════════════════════════════════

    private function verifierPFEAnglaisSansProf(): array
    {
        $sql = "SELECT s.id_stnc,
                       CONCAT(e.nom,' ',e.prenom) AS etudiant,
                       e.langue_pfe
                FROM   Soutenance s
                JOIN   Etudiant   e ON s.id_etud = e.id_etud
                WHERE  LOWER(e.langue_pfe) = 'anglais'
                  AND  s.id_stnc NOT IN (
                           SELECT pa.id_stnc
                           FROM   Participer pa
                           JOIN   Professeur pr ON pa.id_prof = pr.id_prof
                           WHERE  LOWER(pr.specialite) LIKE '%anglais%'
                              OR  LOWER(pr.specialite) LIKE '%langue%'
                       )";

        $rows      = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $anomalies = [];

        foreach ($rows as $row) {
            $anomalies[] = [
                'type'    => 'pfe_anglais_sans_prof',
                'level'   => 'warning',
                'message' => "Soutenance #{$row['id_stnc']} ({$row['etudiant']}) : "
                           . "PFE en anglais mais aucun prof de langue dans le jury",
                'data'    => $row,
            ];
        }

        return $anomalies;
    }

    // ════════════════════════════════════════════════════════════════
    //  CONTRAINTE 6 — Soutenance sans jury
    //  Aucun membre dans la table Participer pour cette soutenance
    // ════════════════════════════════════════════════════════════════

    private function verifierSoutenanceSansJury(): array
    {
        $sql = "SELECT s.id_stnc,
                       CONCAT(e.nom,' ',e.prenom) AS etudiant,
                       c.date_cren, c.heure_debut
                FROM   Soutenance s
                JOIN   Etudiant   e ON s.id_etud = e.id_etud
                JOIN   Creneau    c ON s.id_cren  = c.id_cren
                WHERE  s.id_stnc NOT IN (
                           SELECT DISTINCT id_stnc FROM Participer
                       )";

        $rows      = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $anomalies = [];

        foreach ($rows as $row) {
            $anomalies[] = [
                'type'    => 'soutenance_sans_jury',
                'level'   => 'error',
                'message' => "Soutenance #{$row['id_stnc']} ({$row['etudiant']}) "
                           . "le {$row['date_cren']} à {$row['heure_debut']} "
                           . "n'a aucun membre de jury affecté",
                'data'    => $row,
            ];
        }

        return $anomalies;
    }

    // ════════════════════════════════════════════════════════════════
    //  CONTRAINTE 7 — Étudiant sans encadrant
    // ════════════════════════════════════════════════════════════════

    private function verifierEtudiantSansEncadrant(): array
    {
        $sql = "SELECT id_etud,
                       CONCAT(nom,' ',prenom) AS etudiant,
                       filiere, sujet_pfe
                FROM   Etudiant
                WHERE  id_prof IS NULL";

        $rows      = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $anomalies = [];

        foreach ($rows as $row) {
            $anomalies[] = [
                'type'    => 'etudiant_sans_encadrant',
                'level'   => 'error',
                'message' => "Étudiant #{$row['id_etud']} ({$row['etudiant']}) "
                           . "n'a pas d'encadrant affecté",
                'data'    => $row,
            ];
        }

        return $anomalies;
    }

    // ════════════════════════════════════════════════════════════════
    //  BONUS — Équilibre d'encadrement
    //  Signale si un prof encadre beaucoup plus que la moyenne
    // ════════════════════════════════════════════════════════════════

    private function verifierEquilibreEncadrement(): array
    {
        $sql = "SELECT p.id_prof,
                       CONCAT(p.nom,' ',p.prenom) AS professeur,
                       COUNT(e.id_etud) AS nb_etudiants
                FROM   Professeur p
                LEFT   JOIN Etudiant e ON p.id_prof = e.id_prof
                GROUP  BY p.id_prof, p.nom, p.prenom";

        $rows = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rows)) {
            return [];
        }

        $totals  = array_column($rows, 'nb_etudiants');
        $moyenne = array_sum($totals) / count($totals);

        $anomalies = [];

        foreach ($rows as $row) {
            $nb = (int)$row['nb_etudiants'];

            if ($nb === 0) {
                $anomalies[] = [
                    'type'    => 'prof_sans_etudiant',
                    'level'   => 'warning',
                    'message' => "{$row['professeur']} n'encadre aucun étudiant",
                    'data'    => $row,
                ];
            } elseif ($nb > ($moyenne + 2)) {
                $anomalies[] = [
                    'type'    => 'desequilibre_encadrement',
                    'level'   => 'warning',
                    'message' => "{$row['professeur']} encadre {$nb} étudiants "
                               . "(moyenne = " . round($moyenne, 1) . ") — charge élevée",
                    'data'    => array_merge($row, ['moyenne' => round($moyenne, 1)]),
                ];
            }
        }

        return $anomalies;
    }

    // ════════════════════════════════════════════════════════════════
    //  MÉTHODES PUBLIQUES UTILITAIRES
    //  Permettent au service d'accéder aux statistiques d'encadrement
    // ════════════════════════════════════════════════════════════════

    /**
     * Retourne les stats d'encadrement pour les graphiques.
     * @return array [ 'Nom Prénom' => nb_étudiants, ... ]
     */
    public function getEncadrementStats(): array
    {
        $sql = "SELECT CONCAT(p.nom,' ',p.prenom) AS prof,
                       COUNT(e.id_etud) AS nb
                FROM   Professeur p
                LEFT   JOIN Etudiant e ON p.id_prof = e.id_prof
                GROUP  BY p.id_prof
                ORDER  BY nb DESC";

        $rows   = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $result = [];

        foreach ($rows as $r) {
            $result[$r['prof']] = (int)$r['nb'];
        }

        return $result;
    }

    /**
     * Compte les anomalies par type pour le graphique camembert.
     * @param  array $errors
     * @param  array $warnings
     * @return array [ 'Type' => count, ... ]
     */
    public function getTypesAnomalies(array $errors, array $warnings): array
    {
        $types = [];

        $labelMap = [
            'chevauchement_salle'    => 'Chevauchements salle',
            'double_affectation'     => 'Double affectation',
            'soutenance_sans_jury'   => 'Sans jury',
            'etudiant_sans_encadrant'=> 'Sans encadrant',
            'repos_insuffisant'      => 'Repos insuffisant',
            'jury_incomplet_info'    => 'Jury incomplet',
            'pfe_anglais_sans_prof'  => 'PFE anglais',
            'prof_sans_etudiant'     => 'Prof sans étudiant',
            'desequilibre_encadrement'=> 'Déséquilibre',
        ];

        foreach (array_merge($errors, $warnings) as $item) {
            $label = $labelMap[$item['type']] ?? $item['type'];
            $types[$label] = ($types[$label] ?? 0) + 1;
        }

        return $types;
    }
}