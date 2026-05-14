<?php

namespace App\Services;

use App\Models\Professeur;
use App\Utils\ConstraintChecker;
use PDO;

/**
 * VerificationSvc.php
 * ───────────────────
 * Service métier pour la vérification des contraintes du planning.
 *
 * Rôle : faire le pont entre le contrôleur (VerifCtrl)
 *        et l'utilitaire bas niveau (ConstraintChecker).
 *
 * Retourne des structures normalisées utilisables directement dans les vues.
 *
 * Structure de retour de getAnomalies() :
 * [
 *   'errors'   => [ ['type'=>..., 'level'=>'error',   'message'=>..., 'data'=>...], ... ],
 *   'warnings' => [ ['type'=>..., 'level'=>'warning', 'message'=>..., 'data'=>...], ... ],
 *   'success'  => [ ['type'=>..., 'message'=>...], ... ],
 *   'total'    => int,
 *   'propre'   => bool,
 * ]
 */
class VerificationSvc
{
    private ConstraintChecker $checker;
    private PDO $db;

    public function __construct()
    {
        $this->db      = Professeur::getDB();
        $this->checker = new ConstraintChecker($this->db);
    }

    // ════════════════════════════════════════════════════════════════
    //  MÉTHODE PRINCIPALE
    //  Lance toutes les vérifications et retourne le rapport complet
    // ════════════════════════════════════════════════════════════════

    /**
     * Lance toutes les vérifications via ConstraintChecker.
     *
     * @return array Rapport structuré avec errors / warnings / success / stats
     */
    public function getAnomalies(): array
    {
        $rapport = $this->checker->verifierTout();

        $total = count($rapport['errors']) + count($rapport['warnings']);

        return [
            'errors'        => $rapport['errors'],
            'warnings'      => $rapport['warnings'],
            'success'       => $rapport['success'],
            'total'         => $total,
            'propre'        => $total === 0,
            'typesAnomalies'=> $this->checker->getTypesAnomalies(
                                   $rapport['errors'],
                                   $rapport['warnings']
                               ),
            'encadrStats'   => $this->checker->getEncadrementStats(),
        ];
    }

    // ════════════════════════════════════════════════════════════════
    //  MÉTHODES COMPLÉMENTAIRES
    //  Accessibles séparément depuis le contrôleur si besoin
    // ════════════════════════════════════════════════════════════════

    /**
     * Retourne toutes les soutenances avec leurs détails (pour affichage dans la vue).
     */
    public function getAllSoutenances(): array
    {
        $sql = "SELECT
                    s.id_stnc, s.num_salle,
                    c.date_cren         AS date,
                    c.heure_debut       AS heure,
                    c.heure_fin,
                    e.nom               AS etud_nom,
                    e.prenom            AS etud_prenom,
                    e.filiere,
                    e.langue_pfe,
                    GROUP_CONCAT(DISTINCT p.id_prof)                            AS profs_ids,
                    GROUP_CONCAT(DISTINCT pr.specialite)                        AS profs_specs,
                    GROUP_CONCAT(
                        CONCAT(pr.nom,' ',pr.prenom,'::',p.role_jury)
                        ORDER BY p.role_jury SEPARATOR '|'
                    )                                                           AS jury_raw
                FROM   Soutenance  s
                JOIN   Creneau     c  ON s.id_cren  = c.id_cren
                JOIN   Etudiant    e  ON s.id_etud  = e.id_etud
                LEFT   JOIN Participer  p  ON s.id_stnc  = p.id_stnc
                LEFT   JOIN Professeur  pr ON p.id_prof  = pr.id_prof
                GROUP  BY s.id_stnc, s.num_salle,
                          c.date_cren, c.heure_debut, c.heure_fin,
                          e.nom, e.prenom, e.filiere, e.langue_pfe
                ORDER  BY c.date_cren, c.heure_debut";

        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retourne uniquement les erreurs critiques (chevauchements + double affectation + sans jury + sans encadrant).
     */
    public function checkSalleConflict(array $soutenances): array
    {
        // Délègue à ConstraintChecker via verifierTout
        $rapport = $this->checker->verifierTout();
        return $rapport['errors'];
    }

    /**
     * Retourne uniquement les avertissements.
     */
    public function checkReposInsuffisant(array $soutenances): array
    {
        $rapport = $this->checker->verifierTout();
        return array_filter(
            $rapport['warnings'],
            fn($w) => $w['type'] === 'repos_insuffisant'
        );
    }

    /**
     * Retourne les avertissements d'équilibre d'encadrement.
     */
    public function checkEquilibreEncadrement(): array
    {
        $rapport = $this->checker->verifierTout();
        return array_values(array_filter(
            $rapport['warnings'],
            fn($w) => in_array($w['type'], ['prof_sans_etudiant', 'desequilibre_encadrement'])
        ));
    }

    /**
     * Retourne les contraintes validées avec succès.
     */
    public function checkContraintesOK(array $soutenances): array
    {
        $rapport = $this->checker->verifierTout();
        return array_column($rapport['success'], 'message');
    }

    /**
     * Stats d'encadrement pour graphique.
     */
    public function getEncadrementStats(): array
    {
        return $this->checker->getEncadrementStats();
    }

    /**
     * Types d'anomalies pour graphique camembert.
     */
    public function getTypesAnomalies(array $errors, array $warnings): array
    {
        return $this->checker->getTypesAnomalies($errors, $warnings);
    }

    /**
     * Retourne le nombre total de soutenances planifiées.
     */
    public function countSoutenances(): int
    {
        return (int)$this->db->query("SELECT COUNT(*) FROM Soutenance")->fetchColumn();
    }

    /**
     * Retourne le nombre d'étudiants.
     */
    public function countEtudiants(): int
    {
        return (int)$this->db->query("SELECT COUNT(*) FROM Etudiant")->fetchColumn();
    }

    /**
     * Retourne le nombre de professeurs.
     */
    public function countProfesseurs(): int
    {
        return (int)$this->db->query("SELECT COUNT(*) FROM Professeur")->fetchColumn();
    }

    /**
     * Retourne le nombre de jours distincts de soutenances.
     */
    public function countJours(): int
    {
        return (int)$this->db
            ->query("SELECT COUNT(DISTINCT date_cren) FROM Creneau")
            ->fetchColumn();
    }
}