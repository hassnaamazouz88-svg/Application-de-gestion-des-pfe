<?php

namespace App\Controllers;

use App\Services\PVGeneratorSvc;

/**
 * PVCtrl.php
 * ──────────
 * Contrôleur responsable de la génération des PDFs.
 *
 * Routes attendues (dans index.php) :
 *   ?action=pdf_planning     → generatePlanningPDF()
 *   ?action=pdf_affectation  → generateAffectationPDF()
 *   ?action=pdf_pv&id=X      → generatePV(X)
 */
class PVCtrl
{
    private PVGeneratorSvc $generator;

    public function __construct()
    {
        $this->generator = new PVGeneratorSvc();
    }

    /**
     * Génère et télécharge le PDF du planning complet des soutenances.
     */
    public function generatePlanningPDF(): void
    {
        $this->generator->generatePlanning();
    }

    /**
     * Génère et télécharge le PDF des affectations encadrants.
     */
    public function generateAffectationPDF(): void
    {
        $this->generator->generateAffectation();
    }

    /**
     * Génère et télécharge le PV d'une soutenance spécifique.
     *
     * @param int $idStnc ID de la soutenance (depuis $_GET['id'])
     */
    public function generatePV(int $idStnc): void
    {
        if ($idStnc <= 0) {
            http_response_code(400);
            die("Erreur : identifiant de soutenance invalide.");
        }

        $this->generator->generatePV($idStnc);
    }
}