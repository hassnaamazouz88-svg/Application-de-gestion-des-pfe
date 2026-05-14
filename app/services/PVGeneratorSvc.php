<?php

namespace App\Services;

use Config\Database;
use Dompdf\Dompdf;
use Dompdf\Options;
use PDO;

/**
 * PVGeneratorSvc.php
 * ──────────────────
 * Service de génération de PDFs via Dompdf.
 *
 * PDFs générés :
 *   generatePlanning()     → /public/outputs/planning_soutenances.pdf
 *   generateAffectation()  → /public/outputs/affectation_encadrants.pdf
 *   generatePV(int $id)    → /public/outputs/PV_soutenance_{id}.pdf
 *
 * Chaque méthode :
 *   1. Requête SQL → données
 *   2. Construit le HTML stylisé
 *   3. Appelle makePDF() qui : sauvegarde + force le téléchargement
 *
 * Dépendance Composer : dompdf/dompdf
 *   composer require dompdf/dompdf
 */
class PVGeneratorSvc
{
    private PDO $db;

    /** Dossier de sortie des PDFs */
    private string $outputDir;

    public function __construct()
    {
        $this->db        = Database::getConnection();
        $this->outputDir = __DIR__ . '/../../public/outputs/';

        // Créer le dossier si inexistant
        if (!is_dir($this->outputDir)) {
            mkdir($this->outputDir, 0775, true);
        }
    }

    // ════════════════════════════════════════════════════════════════
    //  MÉTHODE INTERNE — Conversion HTML → PDF + téléchargement
    // ════════════════════════════════════════════════════════════════

    /**
     * Convertit un HTML en PDF, sauvegarde sur disque et envoie au navigateur.
     *
     * @param string $html     HTML complet à convertir
     * @param string $filename Nom du fichier PDF (ex: "planning.pdf")
     * @param string $orient   'portrait' ou 'landscape'
     */
    private function makePDF(string $html, string $filename, string $orient = 'portrait'): void
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', $orient);
        $dompdf->render();

        // ── Sauvegarde sur disque ──────────────────────────────────
        $outputPath = $this->outputDir . $filename;
        file_put_contents($outputPath, $dompdf->output());

        // ── Envoi au navigateur (téléchargement) ──────────────────
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($outputPath));
        header('Cache-Control: no-cache');
        echo $dompdf->output();
        exit;
    }

    // ════════════════════════════════════════════════════════════════
    //  PDF 1 — PLANNING COMPLET DES SOUTENANCES
    //  Affiche : date, heure, salle, étudiant, filière, jury (rôles)
    // ════════════════════════════════════════════════════════════════

    public function generatePlanning(): void
    {
        $sql = "
            SELECT
                c.date_cren,
                c.heure_debut,
                c.heure_fin,
                s.num_salle,
                CONCAT(e.nom,' ',e.prenom)                          AS etudiant,
                e.filiere,
                e.sujet_pfe,
                MAX(CASE WHEN pa.role_jury = 'président'   THEN CONCAT(p.nom,' ',p.prenom) END) AS president,
                MAX(CASE WHEN pa.role_jury = 'rapporteur'  THEN CONCAT(p.nom,' ',p.prenom) END) AS rapporteur,
                MAX(CASE WHEN pa.role_jury = 'encadrant'   THEN CONCAT(p.nom,' ',p.prenom) END) AS encadrant
            FROM   Soutenance  s
            JOIN   Creneau     c  ON s.id_cren  = c.id_cren
            JOIN   Etudiant    e  ON s.id_etud  = e.id_etud
            JOIN   Participer  pa ON s.id_stnc  = pa.id_stnc
            JOIN   Professeur  p  ON pa.id_prof = p.id_prof
            GROUP  BY s.id_stnc, c.date_cren, c.heure_debut, c.heure_fin,
                      s.num_salle, e.nom, e.prenom, e.filiere, e.sujet_pfe
            ORDER  BY c.date_cren, c.heure_debut, s.num_salle
        ";

        $rows = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        // ── Regroupement par date pour les sous-titres ─────────────
        $byDate = [];
        foreach ($rows as $r) {
            $byDate[$r['date_cren']][] = $r;
        }

        // ── Construction du HTML ───────────────────────────────────
        $sections = '';
        $rowIndex = 0;

        foreach ($byDate as $date => $dayRows) {
            $dateFormatee = date('l d/m/Y', strtotime($date));
            $sections .= "
            <tr class='date-row'>
                <td colspan='8'>📅 " . htmlspecialchars($dateFormatee) . "</td>
            </tr>";

            foreach ($dayRows as $r) {
                $rowIndex++;
                $bg = ($rowIndex % 2 === 0) ? '#f8f9ff' : '#ffffff';

                $sections .= "
                <tr style='background:{$bg}'>
                    <td class='center mono'>" . str_pad($rowIndex, 2, '0', STR_PAD_LEFT) . "</td>
                    <td class='center mono'>{$r['heure_debut']} – {$r['heure_fin']}</td>
                    <td class='center'><span class='badge-salle'>Salle {$r['num_salle']}</span></td>
                    <td>
                        <strong>" . htmlspecialchars($r['etudiant']) . "</strong><br>
                        <small class='muted'>" . htmlspecialchars($r['filiere'] ?? '—') . "</small>
                    </td>
                    <td><small>" . htmlspecialchars($r['sujet_pfe'] ?? '—') . "</small></td>
                    <td class='role-cell'>" . htmlspecialchars($r['president'] ?? '—') . "</td>
                    <td class='role-cell'>" . htmlspecialchars($r['rapporteur'] ?? '—') . "</td>
                    <td class='role-cell'>" . htmlspecialchars($r['encadrant'] ?? '—') . "</td>
                </tr>";
            }
        }

        $total = count($rows);

        $html = "<!DOCTYPE html>
<html>
<head>
<meta charset='UTF-8'>
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1a1a2e; background:#fff; }

    /* ── En-tête ── */
    .header { background: linear-gradient(135deg, #1a56db 0%, #0e3fa3 100%);
              color:#fff; padding:20px 24px; margin-bottom:16px; }
    .header h1 { font-size: 18px; font-weight: bold; margin-bottom:4px; }
    .header .sub { font-size: 10px; opacity: .8; }
    .header .meta { font-size: 10px; opacity: .75; margin-top:8px; }

    /* ── Tableau ── */
    table { width: 100%; border-collapse: collapse; font-size: 9px; }
    th {
        background: #1a56db; color: #fff;
        padding: 7px 6px; text-align: left;
        font-size: 8.5px; text-transform: uppercase; letter-spacing: .04em;
    }
    td { padding: 6px; border-bottom: 1px solid #e8ecf5; vertical-align: middle; }

    .date-row td {
        background: #e8f0ff; color: #1a56db;
        font-weight: bold; font-size: 9.5px;
        padding: 5px 8px;
        border-left: 4px solid #1a56db;
    }

    .center { text-align: center; }
    .mono { font-family: 'Courier New', monospace; }
    .muted { color: #6b7280; font-size: 8px; }
    small { color: #6b7280; }

    .badge-salle {
        background: #e8f0ff; color: #1a56db;
        padding: 2px 6px; border-radius: 10px;
        font-weight: bold; font-size: 8px;
    }
    .role-cell { color: #374151; font-size: 8.5px; }

    /* ── Pied de page ── */
    .footer {
        margin-top: 16px; text-align: center;
        color: #9ca3af; font-size: 8px;
        border-top: 1px solid #e5e7eb; padding-top: 8px;
    }
    .kpi-bar {
        display: flex; gap: 12px;
        margin-bottom: 14px;
    }
    .kpi {
        background: #f0f4ff; border: 1px solid #c7d2fe;
        border-radius: 6px; padding: 6px 12px; flex: 1; text-align: center;
    }
    .kpi-label { font-size: 7.5px; color: #6b7280; text-transform: uppercase; }
    .kpi-val   { font-size: 14px; font-weight: bold; color: #1a56db; }
</style>
</head>
<body>

<div class='header'>
    <h1>📋 Planning des Soutenances PFE</h1>
    <div class='sub'>Session 2024-2025 — Gestion des Projets de Fin d'Études</div>
    <div class='meta'>Généré le " . date('d/m/Y à H:i') . " &nbsp;|&nbsp; {$total} soutenance(s) planifiée(s)</div>
</div>

<div class='kpi-bar'>
    <div class='kpi'><div class='kpi-label'>Total soutenances</div><div class='kpi-val'>{$total}</div></div>
    <div class='kpi'><div class='kpi-label'>Jours de soutenance</div><div class='kpi-val'>" . count($byDate) . "</div></div>
    <div class='kpi'><div class='kpi-label'>Document</div><div class='kpi-val'>Planning</div></div>
</div>

<table>
    <thead>
        <tr>
            <th style='width:28px'>#</th>
            <th style='width:80px'>Horaire</th>
            <th style='width:55px'>Salle</th>
            <th style='width:100px'>Étudiant</th>
            <th>Sujet PFE</th>
            <th style='width:80px'>Président</th>
            <th style='width:80px'>Rapporteur</th>
            <th style='width:80px'>Encadrant</th>
        </tr>
    </thead>
    <tbody>
        {$sections}
    </tbody>
</table>

<div class='footer'>
    Planning officiel — Session PFE 2024-2025 | Généré automatiquement le " . date('d/m/Y H:i') . "
</div>

</body>
</html>";

        $this->makePDF($html, 'planning_soutenances.pdf', 'landscape');
    }

    // ════════════════════════════════════════════════════════════════
    //  PDF 2 — AFFECTATION DES ENCADRANTS
    //  Affiche : prof, spécialité, liste des étudiants encadrés
    // ════════════════════════════════════════════════════════════════

    public function generateAffectation(): void
    {
        $sql = "
            SELECT
                CONCAT(p.nom,' ',p.prenom) AS professeur,
                p.specialite,
                GROUP_CONCAT(
                    CONCAT(e.nom,' ',e.prenom,' (',e.filiere,')')
                    ORDER BY e.nom
                    SEPARATOR '\n'
                )                          AS etudiants,
                COUNT(e.id_etud)           AS nb
            FROM   Professeur p
            LEFT   JOIN Etudiant e ON p.id_prof = e.id_prof
            GROUP  BY p.id_prof, p.nom, p.prenom, p.specialite
            ORDER  BY p.nom
        ";

        $rows = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        // Calcul de la moyenne pour indicateur visuel
        $totaux  = array_column($rows, 'nb');
        $moyenne = !empty($totaux) ? round(array_sum($totaux) / count($totaux), 1) : 0;

        $rows_html = '';
        foreach ($rows as $i => $r) {
            $nb    = (int)$r['nb'];
            $color = ($nb > $moyenne + 1) ? '#e02424' : (($nb === 0) ? '#9ca3af' : '#0e9f6e');
            $etuds = $r['etudiants']
                ? nl2br(htmlspecialchars($r['etudiants']))
                : '<em style="color:#9ca3af">Aucun étudiant affecté</em>';

            $rows_html .= "
            <tr>
                <td class='center mono' style='color:#6b7280'>" . str_pad($i + 1, 2, '0', STR_PAD_LEFT) . "</td>
                <td>
                    <strong>" . htmlspecialchars($r['professeur']) . "</strong><br>
                    <small class='muted'>" . htmlspecialchars($r['specialite'] ?? '—') . "</small>
                </td>
                <td class='etud-list'>{$etuds}</td>
                <td class='center'>
                    <span style='background:{$color};color:#fff;padding:3px 8px;border-radius:12px;font-weight:bold;font-size:10px'>{$nb}</span>
                </td>
            </tr>";
        }

        $total_profs  = count($rows);
        $total_etuds  = array_sum($totaux);

        $html = "<!DOCTYPE html>
<html>
<head>
<meta charset='UTF-8'>
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color:#1a1a2e; }

    .header { background: linear-gradient(135deg, #0e9f6e 0%, #065f46 100%);
              color:#fff; padding:20px 24px; margin-bottom:16px; }
    .header h1  { font-size: 18px; font-weight:bold; margin-bottom:4px; }
    .header .sub{ font-size: 10px; opacity:.8; }
    .header .meta{ font-size: 10px; opacity:.75; margin-top:8px; }

    .kpi-bar { display:flex; gap:12px; margin-bottom:14px; }
    .kpi { background:#f0fdf4; border:1px solid #a7f3d0;
           border-radius:6px; padding:6px 12px; flex:1; text-align:center; }
    .kpi-label { font-size:7.5px; color:#6b7280; text-transform:uppercase; }
    .kpi-val   { font-size:14px; font-weight:bold; color:#0e9f6e; }

    table { width:100%; border-collapse:collapse; font-size:9px; }
    th { background:#0e9f6e; color:#fff; padding:7px 6px;
         font-size:8.5px; text-transform:uppercase; text-align:left; }
    td { padding:7px 6px; border-bottom:1px solid #e8ecf5; vertical-align:top; }
    tr:nth-child(even) td { background:#f0fdf4; }

    .center { text-align:center; }
    .mono   { font-family:'Courier New',monospace; }
    .muted  { color:#6b7280; font-size:8px; }
    .etud-list { font-size:8.5px; line-height:1.6; }

    .avg-line { font-size:8px; color:#6b7280; text-align:right;
                margin-bottom:8px; font-style:italic; }
    .footer { margin-top:16px; text-align:center; color:#9ca3af;
              font-size:8px; border-top:1px solid #e5e7eb; padding-top:8px; }
</style>
</head>
<body>

<div class='header'>
    <h1>👨‍🏫 Affectation des Encadrants</h1>
    <div class='sub'>Répartition des étudiants par professeur encadrant — Session 2024-2025</div>
    <div class='meta'>Généré le " . date('d/m/Y à H:i') . "</div>
</div>

<div class='kpi-bar'>
    <div class='kpi'><div class='kpi-label'>Professeurs</div><div class='kpi-val'>{$total_profs}</div></div>
    <div class='kpi'><div class='kpi-label'>Étudiants encadrés</div><div class='kpi-val'>{$total_etuds}</div></div>
    <div class='kpi'><div class='kpi-label'>Moyenne / prof</div><div class='kpi-val'>{$moyenne}</div></div>
</div>

<div class='avg-line'>Moyenne d'encadrement : {$moyenne} étudiant(s) par professeur &nbsp;|&nbsp;
    En rouge = charge supérieure à la moyenne + 1</div>

<table>
    <thead>
        <tr>
            <th style='width:28px'>#</th>
            <th style='width:140px'>Professeur</th>
            <th>Étudiants encadrés</th>
            <th style='width:40px;text-align:center'>Nb</th>
        </tr>
    </thead>
    <tbody>
        {$rows_html}
    </tbody>
</table>

<div class='footer'>
    Affectation officielle des encadrants — Session PFE 2024-2025 | Généré le " . date('d/m/Y H:i') . "
</div>

</body>
</html>";

        $this->makePDF($html, 'affectation_encadrants.pdf');
    }

    // ════════════════════════════════════════════════════════════════
    //  PDF 3 — PROCÈS-VERBAL DE SOUTENANCE (PV)
    //  Affiche : infos étudiant, jury, zone délibération, signatures
    // ════════════════════════════════════════════════════════════════

    /**
     * Génère le PV d'une soutenance spécifique.
     *
     * @param int $idStnc ID de la soutenance dans la table Soutenance
     */
    public function generatePV(int $idStnc): void
    {
        // ── Infos de la soutenance ─────────────────────────────────
        $sql = "
            SELECT
                s.id_stnc,
                CONCAT(e.nom,' ',e.prenom) AS etudiant,
                e.nom            AS etud_nom,
                e.prenom         AS etud_prenom,
                e.filiere,
                e.sujet_pfe,
                e.langue_pfe,
                c.date_cren,
                c.heure_debut,
                c.heure_fin,
                s.num_salle
            FROM   Soutenance s
            JOIN   Etudiant   e ON s.id_etud = e.id_etud
            JOIN   Creneau    c ON s.id_cren  = c.id_cren
            WHERE  s.id_stnc = :id
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idStnc]);
        $stnc = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$stnc) {
            http_response_code(404);
            die("Erreur : Soutenance #{$idStnc} introuvable.");
        }

        // ── Membres du jury ────────────────────────────────────────
        $sqlJury = "
            SELECT
                CONCAT(p.nom,' ',p.prenom) AS nom_complet,
                p.nom, p.prenom,
                p.specialite,
                pa.role_jury
            FROM   Participer  pa
            JOIN   Professeur  p  ON pa.id_prof = p.id_prof
            WHERE  pa.id_stnc = :id
            ORDER  BY FIELD(pa.role_jury,'président','encadrant','rapporteur','jury')
        ";

        $stmtJ = $this->db->prepare($sqlJury);
        $stmtJ->execute([':id' => $idStnc]);
        $jury  = $stmtJ->fetchAll(PDO::FETCH_ASSOC);

        // ── Couleur badge par rôle ─────────────────────────────────
        $roleColors = [
            'président'  => ['bg' => '#dbeafe', 'color' => '#1d4ed8', 'icon' => '👑'],
            'encadrant'  => ['bg' => '#d1fae5', 'color' => '#065f46', 'icon' => '🎓'],
            'rapporteur' => ['bg' => '#fef3c7', 'color' => '#92400e', 'icon' => '📝'],
            'jury'       => ['bg' => '#f3f4f6', 'color' => '#374151', 'icon' => '⚖️'],
        ];

        // ── Lignes jury HTML ───────────────────────────────────────
        $jury_rows = '';
        foreach ($jury as $j) {
            $rc    = $roleColors[strtolower($j['role_jury'])] ?? $roleColors['jury'];
            $role  = ucfirst($j['role_jury']);
            $jury_rows .= "
            <tr>
                <td>
                    <strong>" . htmlspecialchars($j['nom_complet']) . "</strong>
                </td>
                <td>" . htmlspecialchars($j['specialite'] ?? '—') . "</td>
                <td>
                    <span style='background:{$rc['bg']};color:{$rc['color']};
                                 padding:2px 8px;border-radius:10px;font-size:8.5px;font-weight:bold'>
                        {$rc['icon']} {$role}
                    </span>
                </td>
                <td style='width:100px'>
                    <div style='border-bottom:1px solid #9ca3af;height:24px'></div>
                </td>
            </tr>";
        }

        // ── Date formatée ──────────────────────────────────────────
        $dateFormatee = date('d/m/Y', strtotime($stnc['date_cren']));
        $langue       = strtolower($stnc['langue_pfe'] ?? 'français') === 'anglais' ? '🇬🇧 Anglais' : '🇫🇷 Français';

        $html = "<!DOCTYPE html>
<html>
<head>
<meta charset='UTF-8'>
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1a1a2e; }

    /* ── En-tête institutionnel ── */
    .letterhead {
        display: flex; justify-content: space-between;
        align-items: center; padding: 0 0 12px 0;
        border-bottom: 3px solid #1a56db; margin-bottom: 16px;
    }
    .letterhead .school { font-size: 9px; color: #6b7280; }
    .letterhead .school strong { font-size: 11px; color: #1a56db; display:block; }
    .doc-title {
        text-align: center;
        background: linear-gradient(135deg, #1a56db 0%, #0e3fa3 100%);
        color: #fff; padding: 14px 20px; margin-bottom: 16px;
        border-radius: 6px;
    }
    .doc-title h1 { font-size: 16px; font-weight: bold; margin-bottom: 2px; }
    .doc-title .sub { font-size: 9px; opacity: .85; }

    /* ── Section ── */
    .section-title {
        background: #f0f4ff; color: #1a56db;
        padding: 5px 10px; font-size: 9.5px; font-weight: bold;
        text-transform: uppercase; letter-spacing: .06em;
        border-left: 3px solid #1a56db;
        margin: 14px 0 8px 0;
    }

    /* ── Grid info ── */
    .info-grid { width: 100%; border-collapse: collapse; margin-bottom:8px; }
    .info-grid td { padding: 5px 8px; border: 1px solid #e5e7eb; vertical-align: top; font-size: 9.5px; }
    .info-grid .label { color: #6b7280; font-size: 8px; text-transform: uppercase; display:block; margin-bottom:2px; }
    .info-grid .val   { font-weight: bold; color: #111928; }

    /* ── Tableau jury ── */
    .jury-table { width:100%; border-collapse:collapse; }
    .jury-table th {
        background: #1a56db; color:#fff;
        padding: 6px 8px; font-size:8.5px;
        text-transform: uppercase; text-align:left;
    }
    .jury-table td { padding:7px 8px; border-bottom:1px solid #e5e7eb; font-size:9px; }
    tr:nth-child(even) td { background: #f8faff; }

    /* ── Délibération ── */
    .delib-table { width:100%; border-collapse:collapse; margin-top:4px; }
    .delib-table td { padding:8px; border: 1px solid #e5e7eb; font-size:9.5px; }
    .note-box {
        border: 1px solid #e5e7eb; min-height: 60px;
        padding: 8px; border-radius:4px; margin-top:4px;
    }

    /* ── Signatures ── */
    .sig-table { width:100%; border-collapse:collapse; margin-top:30px; }
    .sig-table td {
        text-align:center; padding: 0 12px;
        font-size:9px; color:#374151;
    }
    .sig-line {
        border-top: 1px solid #9ca3af;
        margin-top: 36px; padding-top: 4px;
        font-size: 8px; color: #9ca3af;
    }

    /* ── Footer ── */
    .footer {
        margin-top: 20px; text-align:center;
        color:#9ca3af; font-size: 7.5px;
        border-top: 1px dashed #e5e7eb; padding-top:8px;
    }
    .watermark {
        color: #f3f4f6; font-size: 9px;
        text-align: right; margin-top: 2px;
    }
</style>
</head>
<body>

<!-- ── En-tête institutionnel ──────────────────────────────────── -->
<div class='letterhead'>
    <div class='school'>
        <strong>École Supérieure — Département Informatique</strong>
        Projet de Fin d'Études — Session 2024-2025
    </div>
    <div style='text-align:right;font-size:8.5px;color:#6b7280'>
        PV N° : <strong>PV-{$idStnc}-" . date('Y') . "</strong><br>
        Date d'émission : <strong>" . date('d/m/Y') . "</strong>
    </div>
</div>

<!-- ── Titre ─────────────────────────────────────────────────────── -->
<div class='doc-title'>
    <h1>📄 Procès-Verbal de Soutenance</h1>
    <div class='sub'>Document officiel — Confidentiel jusqu'aux résultats</div>
</div>

<!-- ── Infos soutenance ───────────────────────────────────────────── -->
<div class='section-title'>📋 Informations de la soutenance</div>
<table class='info-grid'>
    <tr>
        <td style='width:50%'>
            <span class='label'>Étudiant(e)</span>
            <span class='val'>" . htmlspecialchars($stnc['etudiant']) . "</span>
        </td>
        <td style='width:50%'>
            <span class='label'>Filière</span>
            <span class='val'>" . htmlspecialchars($stnc['filiere'] ?? '—') . "</span>
        </td>
    </tr>
    <tr>
        <td colspan='2'>
            <span class='label'>Sujet du PFE</span>
            <span class='val'>" . htmlspecialchars($stnc['sujet_pfe'] ?? '—') . "</span>
        </td>
    </tr>
    <tr>
        <td>
            <span class='label'>Langue de soutenance</span>
            <span class='val'>{$langue}</span>
        </td>
        <td>
            <span class='label'>Soutenance N°</span>
            <span class='val'>#{$stnc['id_stnc']}</span>
        </td>
    </tr>
    <tr>
        <td>
            <span class='label'>Date</span>
            <span class='val'>{$dateFormatee}</span>
        </td>
        <td>
            <span class='label'>Horaire</span>
            <span class='val'>{$stnc['heure_debut']} – {$stnc['heure_fin']}</span>
        </td>
    </tr>
    <tr>
        <td>
            <span class='label'>Salle</span>
            <span class='val'>Salle {$stnc['num_salle']}</span>
        </td>
        <td></td>
    </tr>
</table>

<!-- ── Jury ──────────────────────────────────────────────────────── -->
<div class='section-title'>👥 Composition du jury</div>
<table class='jury-table'>
    <thead>
        <tr>
            <th>Nom &amp; Prénom</th>
            <th>Spécialité</th>
            <th>Rôle</th>
            <th style='width:110px'>Signature</th>
        </tr>
    </thead>
    <tbody>
        {$jury_rows}
    </tbody>
</table>

<!-- ── Délibération ──────────────────────────────────────────────── -->
<div class='section-title'>⚖️ Délibération</div>
<table class='delib-table'>
    <tr>
        <td style='width:25%'><strong>Note / 20 :</strong></td>
        <td style='width:25%; border-bottom:2px solid #374151;'> </td>
        <td style='width:25%'><strong>Mention :</strong></td>
        <td style='width:25%; border-bottom:2px solid #374151;'> </td>
    </tr>
    <tr>
        <td colspan='4' style='padding-top:8px'>
            <strong>Décision du jury :</strong>
            <span style='margin-left:12px'>☐ Admis &nbsp;&nbsp; ☐ Ajourné &nbsp;&nbsp; ☐ Félicitations</span>
        </td>
    </tr>
</table>

<!-- ── Observations ──────────────────────────────────────────────── -->
<div class='section-title'>💬 Observations du jury</div>
<div class='note-box'> </div>

<!-- ── Signatures ────────────────────────────────────────────────── -->
<table class='sig-table'>
    <tr>
        <td>
            <strong>Le Président du jury</strong>
            <div class='sig-line'>Nom &amp; Signature</div>
        </td>
        <td>
            <strong>L'Encadrant</strong>
            <div class='sig-line'>Nom &amp; Signature</div>
        </td>
        <td>
            <strong>Le Rapporteur</strong>
            <div class='sig-line'>Nom &amp; Signature</div>
        </td>
    </tr>
</table>

<!-- ── Pied de page ───────────────────────────────────────────────── -->
<div class='footer'>
    Procès-Verbal officiel de soutenance PFE — École Supérieure — Session 2024-2025<br>
    Document généré automatiquement le " . date('d/m/Y à H:i') . " | Référence : PV-{$idStnc}-" . date('Y') . "
</div>

</body>
</html>";

        $this->makePDF($html, "PV_soutenance_{$idStnc}.pdf");
    }
}