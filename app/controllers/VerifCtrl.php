<?php

namespace App\Controllers;

use App\Services\VerificationSvc;

/**
 * VerifCtrl.php
 * ─────────────
 * Contrôleur MVC pour la page de vérification des contraintes.
 *
 * Actions disponibles :
 *   index()  → affiche le tableau de bord de vérification (verification.view.php)
 *   ajax()   → retourne le rapport en JSON (pour rechargement dynamique)
 */
class VerifCtrl
{
    private VerificationSvc $service;

    public function __construct()
    {
        $this->service = new VerificationSvc();
    }

    // ════════════════════════════════════════════════════════════════
    //  ACTION PRINCIPALE
    //  Prépare toutes les variables et charge la vue
    // ════════════════════════════════════════════════════════════════

    public function index(): void
    {
        // ── 1. Lancer toutes les vérifications ────────────────────
        $rapport = $this->service->getAnomalies();

        $erreursCritiques = $rapport['errors'];
        $avertissements   = $rapport['warnings'];
        $infos            = $rapport['success'];

        // ── 2. Stats pour graphiques ──────────────────────────────
        $encadrStats    = $rapport['encadrStats'];
        $typesAnomalies = $rapport['typesAnomalies'];

        // ── 3. Compteurs KPI ──────────────────────────────────────
        $totalErreurs = count($erreursCritiques) + count($avertissements);
        $nbOk         = count($infos);
        $nbEtudiants  = $this->service->countEtudiants();
        $nbProfs      = $this->service->countProfesseurs();
        $nbSoutenances= $this->service->countSoutenances();

        // ── 4. Récupérer les soutenances pour la vue ──────────────
        $soutenances  = $this->service->getAllSoutenances();

        // ── 5. Charger la vue ─────────────────────────────────────
        require __DIR__ . '/../../views/verification.view.php';
    }

    // ════════════════════════════════════════════════════════════════
    //  ACTION AJAX
    //  Retourne le rapport en JSON (pour rechargement sans F5)
    // ════════════════════════════════════════════════════════════════

    public function ajax(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $rapport = $this->service->getAnomalies();

        echo json_encode([
            'success'        => true,
            'nbErrors'       => count($rapport['errors']),
            'nbWarnings'     => count($rapport['warnings']),
            'nbOk'           => count($rapport['success']),
            'propre'         => $rapport['propre'],
            'errors'         => array_column($rapport['errors'],   'message'),
            'warnings'       => array_column($rapport['warnings'], 'message'),
            'infos'          => array_column($rapport['success'],  'message'),
            'typesAnomalies' => $rapport['typesAnomalies'],
            'encadrStats'    => $rapport['encadrStats'],
        ]);

        exit;
    }
}