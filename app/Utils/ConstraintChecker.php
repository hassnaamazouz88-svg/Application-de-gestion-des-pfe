<?php

namespace App\Utils;

/**
 * ConstraintChecker
 * ─────────────────────────────────────────────────────────────
 * Classe utilitaire statique qui regroupe TOUTES les règles
 * de validation des contraintes du planning PFE.
 *
 * Principe Open/Closed :
 *   → Ouverte à l'extension  (ajouter une nouvelle règle = ajouter une méthode)
 *   → Fermée à la modification (les règles existantes ne changent pas)
 *
 * Utilisée par : VerificationSvc.php
 * ─────────────────────────────────────────────────────────────
 */
class ConstraintChecker
{
    // ══════════════════════════════════════════════════════════
    //  CONSTANTES DES RÈGLES MÉTIER
    // ══════════════════════════════════════════════════════════

    /** Durée d'une soutenance en heures */
    const DUREE_SOUTENANCE_H = 1;

    /** Repos minimum entre 2 soutenances d'un même prof (en heures) */
    const REPOS_MIN_H = 1;

    /** Nombre minimum de profs informatique dans un jury */
    const MIN_PROFS_INFO = 2;

    /** Spécialité informatique (sous-chaîne à détecter) */
    const SPEC_INFORMATIQUE = 'informatique';

    /** Spécialité langue anglais (sous-chaînes à détecter) */
    const SPEC_ANGLAIS = ['anglais', 'langue'];

    /** Créneaux horaires autorisés HH:MM */
    const CRENEAUX_AUTORISES = [
        '09:00', '10:00', '11:00',   // matin
        '14:00', '15:00', '16:00', '17:00'  // après-midi
    ];

    /** Nombre maximum de jours de soutenance */
    const NB_JOURS_MAX = 3;

    /** Encadrement min et max par prof */
    const ENCADREMENT_MIN = 1;
    const ENCADREMENT_MAX = 6;


    // ══════════════════════════════════════════════════════════
    //  A. CONTRAINTES SALLES
    // ══════════════════════════════════════════════════════════

    /**
     * Vérifie s'il y a un chevauchement dans une salle
     * (même salle, même date, même heure → 2 soutenances)
     *
     * @param array $soutenances  Liste des soutenances (chaque entrée a : date, heure, num_salle)
     * @return array              Liste des messages d'erreur
     */
    public static function checkChevauchementSalle(array $soutenances): array
    {
        $errors = [];
        $seen   = [];

        foreach ($soutenances as $s) {
            $key = $s['date'] . '|' . $s['heure'] . '|' . $s['num_salle'];

            if (isset($seen[$key])) {
                $errors[] = [
                    'type'    => 'chevauchement_salle',
                    'niveau'  => 'critique',
                    'message' => "Chevauchement salle {$s['num_salle']} : "
                               . "deux soutenances le {$s['date']} à {$s['heure']}",
                ];
            }
            $seen[$key] = true;
        }

        return $errors;
    }


    // ══════════════════════════════════════════════════════════
    //  B. CONTRAINTES PROFESSEURS
    // ══════════════════════════════════════════════════════════

    /**
     * Vérifie si un professeur est affecté à deux soutenances
     * au même horaire (double affectation)
     *
     * @param array $soutenances  Chaque entrée doit avoir : date, heure, profs_ids
     * @return array
     */
    public static function checkDoubleAffectationProf(array $soutenances): array
    {
        $errors    = [];
        $profSlots = [];

        foreach ($soutenances as $s) {
            $profIds = array_map('trim', explode(',', $s['profs_ids'] ?? ''));
            $profNoms = explode('|', $s['profs_noms'] ?? '');

            foreach ($profIds as $i => $profId) {
                if (empty($profId)) continue;

                $key = $profId . '|' . $s['date'] . '|' . $s['heure'];
                $nom = $profNoms[$i] ?? "Prof. #$profId";

                if (isset($profSlots[$key])) {
                    $errors[] = [
                        'type'    => 'double_affectation',
                        'niveau'  => 'critique',
                        'message' => "Double affectation : $nom est dans "
                                   . "2 salles le {$s['date']} à {$s['heure']}",
                    ];
                }
                $profSlots[$key] = $nom;
            }
        }

        return array_values(self::deduplicate($errors));
    }

    /**
     * Vérifie que chaque prof a au moins REPOS_MIN_H heure de repos
     * entre deux soutenances consécutives le même jour
     *
     * @param array $soutenances
     * @return array
     */
    public static function checkReposInsuffisant(array $soutenances): array
    {
        $errors    = [];
        $profSlots = [];

        // Grouper les créneaux par prof
        foreach ($soutenances as $s) {
            $profIds  = array_map('trim', explode(',', $s['profs_ids'] ?? ''));
            $profNoms = explode('|', $s['profs_noms'] ?? '');

            foreach ($profIds as $i => $profId) {
                if (empty($profId)) continue;
                $nom = $profNoms[$i] ?? "Prof. #$profId";

                $profSlots[$profId]['nom']    = $nom;
                $profSlots[$profId]['slots'][] = [
                    'date'  => $s['date'],
                    'heure' => $s['heure'],
                ];
            }
        }

        // Vérifier le repos entre créneaux consécutifs
        foreach ($profSlots as $profId => $data) {
            $slots = $data['slots'];
            $nom   = $data['nom'];

            // Trier par date puis heure
            usort($slots, fn($a, $b) =>
                strcmp($a['date'] . $a['heure'], $b['date'] . $b['heure'])
            );

            for ($i = 0; $i < count($slots) - 1; $i++) {
                // Comparer seulement les soutenances du même jour
                if ($slots[$i]['date'] !== $slots[$i + 1]['date']) continue;

                $h1   = strtotime($slots[$i]['heure']);
                $h2   = strtotime($slots[$i + 1]['heure']);
                $diff = ($h2 - $h1) / 3600;

                if ($diff < self::REPOS_MIN_H) {
                    $errors[] = [
                        'type'    => 'repos_insuffisant',
                        'niveau'  => 'avertissement',
                        'message' => "Repos insuffisant : $nom — "
                                   . "seulement {$diff}h entre {$slots[$i]['heure']} "
                                   . "et {$slots[$i+1]['heure']} le {$slots[$i]['date']}",
                    ];
                }
            }
        }

        return $errors;
    }


    // ══════════════════════════════════════════════════════════
    //  C. CONTRAINTES JURY
    // ══════════════════════════════════════════════════════════

    /**
     * Vérifie que chaque jury a au moins MIN_PROFS_INFO profs informatique
     *
     * @param array $soutenances  Chaque entrée doit avoir : profs_specs, etudiant
     * @return array
     */
    public static function checkJuryInformatique(array $soutenances): array
    {
        $errors = [];

        foreach ($soutenances as $s) {
            $specs  = array_map('trim', explode(',', $s['profs_specs'] ?? ''));
            $nbInfo = count(array_filter($specs, fn($sp) =>
                stripos($sp, self::SPEC_INFORMATIQUE) !== false
            ));

            if ($nbInfo < self::MIN_PROFS_INFO) {
                $etud = $s['etud_nom'] ?? 'Étudiant inconnu';
                $errors[] = [
                    'type'    => 'jury_informatique',
                    'niveau'  => 'critique',
                    'message' => "Jury incomplet pour $etud : "
                               . "seulement $nbInfo prof(s) informatique "
                               . "(minimum requis : " . self::MIN_PROFS_INFO . ")",
                ];
            }
        }

        return $errors;
    }

    /**
     * Vérifie que les PFE rédigées en anglais ont un prof de langue anglais dans le jury
     *
     * @param array $soutenances  Chaque entrée doit avoir : langue_pfe, profs_specs, etud_nom
     * @return array
     */
    public static function checkJuryAnglais(array $soutenances): array
    {
        $errors = [];

        foreach ($soutenances as $s) {
            if (strtolower(trim($s['langue_pfe'] ?? '')) !== 'anglais') continue;

            $specs    = array_map('trim', explode(',', $s['profs_specs'] ?? ''));
            $hasLang  = false;

            foreach ($specs as $sp) {
                foreach (self::SPEC_ANGLAIS as $keyword) {
                    if (stripos($sp, $keyword) !== false) {
                        $hasLang = true;
                        break 2;
                    }
                }
            }

            if (!$hasLang) {
                $etud = $s['etud_nom'] ?? 'Étudiant inconnu';
                $errors[] = [
                    'type'    => 'jury_anglais',
                    'niveau'  => 'critique',
                    'message' => "PFE en anglais de $etud : "
                               . "aucun professeur de langue anglais dans le jury",
                ];
            }
        }

        return $errors;
    }


    // ══════════════════════════════════════════════════════════
    //  D. CONTRAINTES HORAIRES
    // ══════════════════════════════════════════════════════════

    /**
     * Vérifie que toutes les soutenances sont dans les créneaux autorisés
     *
     * @param array $soutenances  Chaque entrée doit avoir : heure, etud_nom, date
     * @return array
     */
    public static function checkCreneauxAutorises(array $soutenances): array
    {
        $errors = [];

        foreach ($soutenances as $s) {
            $heure = substr($s['heure'] ?? '', 0, 5); // HH:MM
            $etud  = $s['etud_nom'] ?? 'Étudiant inconnu';

            if (!in_array($heure, self::CRENEAUX_AUTORISES)) {
                $errors[] = [
                    'type'    => 'creneau_invalide',
                    'niveau'  => 'critique',
                    'message' => "Créneau invalide $heure pour $etud le {$s['date']} "
                               . "— créneaux autorisés : "
                               . implode(', ', self::CRENEAUX_AUTORISES),
                ];
            }
        }

        return $errors;
    }

    /**
     * Vérifie que le nombre total de jours ne dépasse pas NB_JOURS_MAX
     *
     * @param array $soutenances
     * @return array
     */
    public static function checkNombreJours(array $soutenances): array
    {
        $errors = [];
        $dates  = array_unique(array_column($soutenances, 'date'));
        $nbJours = count($dates);

        if ($nbJours > self::NB_JOURS_MAX) {
            $errors[] = [
                'type'    => 'nb_jours_depasse',
                'niveau'  => 'avertissement',
                'message' => "Le planning s'étale sur $nbJours jours "
                           . "(maximum autorisé : " . self::NB_JOURS_MAX . " jours)",
            ];
        }

        return $errors;
    }


    // ══════════════════════════════════════════════════════════
    //  E. CONTRAINTES ENCADREMENT
    // ══════════════════════════════════════════════════════════

    /**
     * Vérifie l'équilibre de l'encadrement entre professeurs
     *
     * @param array $encadrStats  [ 'Nom Prof' => nb_etudiants, ... ]
     * @return array
     */
    public static function checkEquilibreEncadrement(array $encadrStats): array
    {
        $errors = [];

        if (empty($encadrStats)) return $errors;

        $avg = array_sum($encadrStats) / count($encadrStats);

        foreach ($encadrStats as $prof => $nb) {
            if ($nb === 0) {
                $errors[] = [
                    'type'    => 'encadrement_zero',
                    'niveau'  => 'avertissement',
                    'message' => "$prof n'encadre aucun étudiant — vérifier disponibilité",
                ];
            } elseif ($nb < self::ENCADREMENT_MIN) {
                $errors[] = [
                    'type'    => 'encadrement_faible',
                    'niveau'  => 'avertissement',
                    'message' => "$prof encadre seulement $nb étudiant(s) "
                               . "(minimum recommandé : " . self::ENCADREMENT_MIN . ")",
                ];
            } elseif ($nb > self::ENCADREMENT_MAX) {
                $errors[] = [
                    'type'    => 'encadrement_excessif',
                    'niveau'  => 'critique',
                    'message' => "$prof encadre $nb étudiants "
                               . "(maximum autorisé : " . self::ENCADREMENT_MAX . ")",
                ];
            } elseif ($nb > $avg + 1.5) {
                $errors[] = [
                    'type'    => 'encadrement_desequilibre',
                    'niveau'  => 'avertissement',
                    'message' => "Déséquilibre : $prof encadre $nb étudiants "
                               . "(moyenne = " . round($avg, 1) . ")",
                ];
            }
        }

        return $errors;
    }


    // ══════════════════════════════════════════════════════════
    //  F. MÉTHODE PRINCIPALE — Lancer TOUTES les vérifications
    // ══════════════════════════════════════════════════════════

    /**
     * Lance toutes les vérifications d'un coup et retourne
     * un tableau structuré avec critiques, avertissements, et infos OK
     *
     * @param array $soutenances   Résultat de VerificationSvc::getAllSoutenances()
     * @param array $encadrStats   Résultat de VerificationSvc::getEncadrementStats()
     * @return array [
     *     'critiques'       => [...],
     *     'avertissements'  => [...],
     *     'ok'              => [...],
     * ]
     */
    public static function runAll(array $soutenances, array $encadrStats): array
    {
        $critiques      = [];
        $avertissements = [];
        $ok             = [];

        // ── Vérifications critiques ──
        $checks = [
            self::checkChevauchementSalle($soutenances),
            self::checkDoubleAffectationProf($soutenances),
            self::checkJuryInformatique($soutenances),
            self::checkJuryAnglais($soutenances),
            self::checkCreneauxAutorises($soutenances),
        ];

        // ── Vérifications avertissement ──
        $checksWarn = [
            self::checkReposInsuffisant($soutenances),
            self::checkNombreJours($soutenances),
            self::checkEquilibreEncadrement($encadrStats),
        ];

        // Trier par niveau
        foreach (array_merge(...$checks) as $err) {
            if ($err['niveau'] === 'critique') {
                $critiques[] = $err['message'];
            } else {
                $avertissements[] = $err['message'];
            }
        }

        foreach (array_merge(...$checksWarn) as $err) {
            if ($err['niveau'] === 'critique') {
                $critiques[] = $err['message'];
            } else {
                $avertissements[] = $err['message'];
            }
        }

        // ── Contraintes OK ──
        if (empty(self::checkChevauchementSalle($soutenances))) {
            $ok[] = "✅ Aucun chevauchement de salle détecté";
        }
        if (empty(self::checkDoubleAffectationProf($soutenances))) {
            $ok[] = "✅ Aucune double affectation de professeur";
        }
        if (empty(self::checkJuryInformatique($soutenances))) {
            $ok[] = "✅ Tous les jurys ont au moins " . self::MIN_PROFS_INFO . " profs informatique";
        }
        if (empty(self::checkJuryAnglais($soutenances))) {
            $ok[] = "✅ Toutes les PFE en anglais ont un prof de langue dans le jury";
        }
        if (empty(self::checkReposInsuffisant($soutenances))) {
            $ok[] = "✅ Tous les professeurs ont le repos minimum respecté";
        }

        return [
            'critiques'      => array_unique($critiques),
            'avertissements' => array_unique($avertissements),
            'ok'             => $ok,
        ];
    }


    // ══════════════════════════════════════════════════════════
    //  MÉTHODE UTILITAIRE PRIVÉE
    // ══════════════════════════════════════════════════════════

    /**
     * Supprime les doublons dans un tableau d'erreurs (basé sur le message)
     */
    private static function deduplicate(array $errors): array
    {
        $seen   = [];
        $result = [];

        foreach ($errors as $err) {
            if (!in_array($err['message'], $seen)) {
                $seen[]   = $err['message'];
                $result[] = $err;
            }
        }

        return $result;
    }
}