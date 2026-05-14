<?php

/**
 * index.php — Routeur principal de l'application
 * ─────────────────────────────────────────────────
 * Point d'entrée unique (Front Controller).
 * Gère le chargement des dépendances et le dispatch
 * vers le bon contrôleur selon le paramètre ?action=
 *
 * URL exemples :
 *   index.php?action=import
 *   index.php?action=affectation
 *   index.php?action=verifcation
 *   index.php?action=pdf_planning
 *   index.php?action=pdf_affectation
 *   index.php?action=pdf_pv&id=5
 */

declare(strict_types=1);

/* ══════════════════════════════════════════════════════
   1. AUTOLOAD — charge les classes automatiquement
══════════════════════════════════════════════════════ */

// Si Composer est disponible :
// require_once __DIR__ . '/vendor/autoload.php';

// Sinon, autoload manuel PSR-4 simplifié :
spl_autoload_register(function (string $class): void {
    // Convertir le namespace en chemin de fichier
    // App\Controllers\PVCtrl → app/Controllers/PVCtrl.php
    $map = [
        'App\\'    => __DIR__ . '/app/',
        'Config\\' => __DIR__ . '/config/',
    ];

    foreach ($map as $prefix => $baseDir) {
        if (strncmp($class, $prefix, strlen($prefix)) === 0) {
            $relative = substr($class, strlen($prefix));
            $file     = $baseDir . str_replace('\\', '/', $relative) . '.php';
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }
});

/* ══════════════════════════════════════════════════════
   2. SESSION (pour les messages flash futurs)
══════════════════════════════════════════════════════ */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ══════════════════════════════════════════════════════
   3. ROUTING
══════════════════════════════════════════════════════ */
$action = $_GET['action'] ?? 'dashboard';

switch ($action) {

    // ── Tableau de bord ────────────────────────────────
    case 'dashboard':
        require_once __DIR__ . '/views/dashboard.view.php';
        break;

    // ── Planning ───────────────────────────────────────
    case 'planning':
        require_once __DIR__ . '/views/planning.view.php';
        break;

    // ── Vérification ──────────────────────────────────
    case 'verification':
        $ctrl = new \App\Controllers\VerifCtrl();
        $ctrl->index();
        break;

    case 'verification_ajax':
        $ctrl = new \App\Controllers\VerifCtrl();
        $ctrl->ajax();
        break;

    // ── Import Excel ───────────────────────────────────
    case 'import':
        $ctrl = new \App\Controllers\ImportCtrl();
        $ctrl->index();
        break;

    case 'import_etudiants':
        $ctrl = new \App\Controllers\ImportCtrl();
        $ctrl->importEtudiants();
        break;

    case 'import_professeurs':
        $ctrl = new \App\Controllers\ImportCtrl();
        $ctrl->importProfesseurs();
        break;

    // ── Affectation ────────────────────────────────────
    case 'affectation':
        $ctrl = new \App\Controllers\AffectationCtrl();
        $ctrl->lancer();
        break;

    // ── Génération PDFs ────────────────────────────────
    case 'pdf_planning':
        $ctrl = new \App\Controllers\PVCtrl();
        $ctrl->generatePlanningPDF();
        break;

    case 'pdf_affectation':
        $ctrl = new \App\Controllers\PVCtrl();
        $ctrl->generateAffectationPDF();
        break;

    case 'pdf_pv':
        $idStnc = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($idStnc <= 0) {
            http_response_code(400);
            die('ID de soutenance invalide.');
        }
        $ctrl = new \App\Controllers\PVCtrl();
        $ctrl->generatePV($idStnc);
        break;

    // ── Refresh / fallback ─────────────────────────────
    case 'refresh':
        header('Location: index.php?action=dashboard');
        exit;

    default:
        http_response_code(404);
        echo "<h2>Page introuvable (action='" . htmlspecialchars($action) . "')</h2>";
        echo "<a href='index.php'>← Retour au dashboard</a>";
        break;
}