<?php
namespace App\Services;

use App\Utils\ConstraintChecker;   // ← ConstraintChecker est importé ici
use Config\Database;
use PDO;

class VerificationSvc
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // ══════════════════════════════════════════════════════════
    //  REQUÊTES BASE DE DONNÉES
    // ══════════════════════════════════════════════════════════

    /**
     * Récupère toutes les soutenances avec toutes les infos nécessaires
     * pour les vérifications (profs, spécialités, horaires, salles...)
     */
    public function getAllSoutenances(): array
    {
        $sql = "
            SELECT 
                s.id_stnc,
                s.num_salle,
                c.date_cren                                         AS date,
                c.heure_debut                                       AS heure,
                c.heure_fin,
                e.nom                                               AS etud_nom,
                e.prenom                                            AS etud_prenom,
                e.filiere,
                e.langue_pfe,
                GROUP_CONCAT(p.id_prof)                             AS profs_ids,
                GROUP_CONCAT(p.specialite)                          AS profs_specs,
                GROUP_CONCAT(
                    CONCAT(p.nom,' ',p.prenom)
                    ORDER BY pa.role_jury SEPARATOR '|'
                )                                                   AS profs_noms,
                GROUP_CONCAT(pa.role_jury ORDER BY pa.role_jury SEPARATOR '|') AS roles
            FROM Soutenance  s
            JOIN Creneau     c  ON s.id_cren  = c.id_cren
            JOIN Etudiant    e  ON s.id_etud  = e.id_etud
            JOIN Participer  pa ON s.id_stnc  = pa.id_stnc
            JOIN Professeur  p  ON pa.id_prof = p.id_prof
            GROUP BY s.id_stnc
            ORDER BY c.date_cren, c.heure_debut
        ";

        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère le planning complet formaté pour la vue planning.view.php
     */
    public function getPlanningComplet(): array
    {
        $sql = "
            SELECT 
                c.date_cren                                         AS date,
                c.heure_debut                                       AS heure,
                c.heure_fin,
                s.num_salle                                         AS salle,
                CONCAT(e.nom,' ',e.prenom)                          AS etudiant,
                e.filiere,
                GROUP_CONCAT(
                    CONCAT(p.nom,' ',p.prenom,'::',pa.role_jury)
                    ORDER BY pa.role_jury SEPARATOR '|'
                )                                                   AS jury_raw
            FROM Soutenance  s
            JOIN Creneau     c  ON s.id_cren  = c.id_cren
            JOIN Etudiant    e  ON s.id_etud  = e.id_etud
            JOIN Participer  pa ON s.id_stnc  = pa.id_stnc
            JOIN Professeur  p  ON pa.id_prof = p.id_prof
            GROUP BY s.id_stnc
            ORDER BY c.date_cren, c.heure_debut, s.num_salle
        ";

        $rows    = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $planning = [];

        foreach ($rows as $r) {
            $jury = [];
            foreach (explode('|', $r['jury_raw']) as $item) {
                [$nom, $role] = explode('::', $item);
                $classeMap = [
                    'encadrant'  => 'role-enc',
                    'président'  => 'role-prés',
                    'rapporteur' => 'role-rapp',
                    'jury'       => 'role-rapp',
                ];
                $jury[] = [
                    'nom'    => $nom,
                    'role'   => ucfirst($role),
                    'classe' => $classeMap[strtolower($role)] ?? 'role-rapp',
                ];
            }

            $planning[] = [
                'date'     => $r['date'],
                'heure'    => substr($r['heure'], 0, 5) . ' - ' . substr($r['heure_fin'], 0, 5),
                'salle'    => 'Salle ' . $r['salle'],
                'etudiant' => $r['etudiant'],
                'filiere'  => $r['filiere'],
                'jury'     => $jury,
            ];
        }

        return $planning;
    }

    /**
     * Stats pour les KPI cards du dashboard
     */
    public function getDashboardStats(): array
    {
        return [
            'nbEtudiants'   => (int)$this->db->query("SELECT COUNT(*) FROM Etudiant")->fetchColumn(),
            'nbProfs'       => (int)$this->db->query("SELECT COUNT(*) FROM Professeur")->fetchColumn(),
            'nbSoutenances' => (int)$this->db->query("SELECT COUNT(*) FROM Soutenance")->fetchColumn(),
            'nbJours'       => (int)$this->db->query("SELECT COUNT(DISTINCT date_cren) FROM Creneau")->fetchColumn(),
        ];
    }

    /**
     * Répartition des étudiants par filière (pour graphique dashboard)
     */
    public function getFilieres(): array
    {
        $sql = "
            SELECT filiere AS nom, COUNT(*) AS nb 
            FROM Etudiant 
            WHERE filiere IS NOT NULL 
            GROUP BY filiere
        ";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Top 5 profs avec le plus de soutenances (pour graphique dashboard)
     */
    public function getSoutenancesParProf(): array
    {
        $sql = "
            SELECT CONCAT(p.nom,' ',p.prenom) AS prof, COUNT(pa.id_stnc) AS nb
            FROM Professeur  p
            LEFT JOIN Participer pa ON p.id_prof = pa.id_prof
            GROUP BY p.id_prof
            ORDER BY nb DESC
            LIMIT 5
        ";
        $rows   = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $result = [];
        foreach ($rows as $r) {
            $result[$r['prof']] = (int)$r['nb'];
        }
        return $result;
    }

    /**
     * Nombre d'étudiants encadrés par chaque prof (pour graphique)
     */
    public function getEncadrementStats(): array
    {
        $sql = "
            SELECT CONCAT(p.nom,' ',p.prenom) AS prof, COUNT(e.id_etud) AS nb
            FROM Professeur p
            LEFT JOIN Etudiant e ON p.id_prof = e.id_prof
            GROUP BY p.id_prof
            ORDER BY nb DESC
        ";
        $rows   = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $result = [];
        foreach ($rows as $r) {
            $result[$r['prof']] = (int)$r['nb'];
        }
        return $result;
    }


    // ══════════════════════════════════════════════════════════
    //  VÉRIFICATIONS — délèguent à ConstraintChecker
    //  (VerificationSvc récupère les données de la DB,
    //   ConstraintChecker applique les règles sur ces données)
    // ══════════════════════════════════════════════════════════

    /**
     * Vérifie les chevauchements de salles
     * → appelle ConstraintChecker::checkChevauchementSalle()
     */
    public function checkSalleConflict(array $soutenances): array
    {
        $erreurs = ConstraintChecker::checkChevauchementSalle($soutenances);

        // ConstraintChecker retourne des tableaux avec 'message'
        // on extrait juste les messages pour la vue
        return array_column($erreurs, 'message');
    }

    /**
     * Vérifie la double affectation d'un prof au même horaire
     * → appelle ConstraintChecker::checkDoubleAffectationProf()
     */
    public function checkProfDoubleAffectation(array $soutenances): array
    {
        $erreurs = ConstraintChecker::checkDoubleAffectationProf($soutenances);
        return array_column($erreurs, 'message');
    }

    /**
     * Vérifie le repos insuffisant entre 2 soutenances d'un même prof
     * → appelle ConstraintChecker::checkReposInsuffisant()
     */
    public function checkReposInsuffisant(array $soutenances): array
    {
        $erreurs = ConstraintChecker::checkReposInsuffisant($soutenances);
        return array_column($erreurs, 'message');
    }

    /**
     * Vérifie l'équilibre de l'encadrement entre profs
     * → récupère les stats depuis la DB
     * → appelle ConstraintChecker::checkEquilibreEncadrement()
     */
    public function checkEquilibreEncadrement(): array
    {
        // 1. On récupère les stats depuis la DB
        $stats = $this->getEncadrementStats();

        // 2. On passe les stats à ConstraintChecker qui applique les règles
        $erreurs = ConstraintChecker::checkEquilibreEncadrement($stats);

        return array_column($erreurs, 'message');
    }

    /**
     * Vérifie les contraintes jury (informatique + anglais)
     * → appelle ConstraintChecker::checkJuryInformatique() et checkJuryAnglais()
     */
    public function checkContraintesJury(array $soutenances): array
    {
        $erreurs = array_merge(
            ConstraintChecker::checkJuryInformatique($soutenances),
            ConstraintChecker::checkJuryAnglais($soutenances)
        );
        return array_column($erreurs, 'message');
    }

    /**
     * Retourne les contraintes qui sont OK (pour la section verte de la vue)
     * → utilise ConstraintChecker::runAll() qui vérifie tout d'un coup
     */
    public function checkContraintesOK(array $soutenances): array
    {
        // 1. Récupérer les stats d'encadrement depuis la DB
        $stats = $this->getEncadrementStats();

        // 2. ConstraintChecker::runAll() lance TOUTES les vérifications
        //    et retourne un tableau avec 'critiques', 'avertissements', 'ok'
        $result = ConstraintChecker::runAll($soutenances, $stats);

        // 3. On retourne seulement la liste des contraintes OK
        return $result['ok'];
    }

    /**
     * Prépare les données pour le pie chart des types d'anomalies
     */
    public function getTypesAnomalies(array $critiques, array $avertissements): array
    {
        $types = [
            'Chevauchements'       => 0,
            'Double affectation'   => 0,
            'Repos insuffisant'    => 0,
            'Jury incomplet'       => 0,
            'Déséquilibre encadr.' => 0,
        ];

        foreach ($critiques as $msg) {
            if (stripos($msg, 'chevauchement') !== false) $types['Chevauchements']++;
            if (stripos($msg, 'double')        !== false) $types['Double affectation']++;
            if (stripos($msg, 'jury')          !== false) $types['Jury incomplet']++;
        }
        foreach ($avertissements as $msg) {
            if (stripos($msg, 'repos')     !== false) $types['Repos insuffisant']++;
            if (stripos($msg, 'équilibre') !== false) $types['Déséquilibre encadr.']++;
        }

        // Retirer les types avec 0 anomalie (pour ne pas les afficher dans le graphe)
        return array_filter($types);
    }
}