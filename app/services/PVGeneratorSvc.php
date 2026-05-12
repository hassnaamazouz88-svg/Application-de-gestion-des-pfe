<?php
namespace App\Services;

use Config\Database;
use Dompdf\Dompdf;
use Dompdf\Options;
use PDO;

class PVGeneratorSvc
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    private function makePDF(string $html, string $filename): void
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Sauvegarder dans /public/outputs/
        $outputPath = __DIR__ . '/../../public/outputs/' . $filename;
        file_put_contents($outputPath, $dompdf->output());

        // Télécharger
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo $dompdf->output();
        exit;
    }

    // ── PDF Planning complet ──
    public function generatePlanning(): void
    {
        $sql = "
            SELECT 
                c.date_cren, c.heure_debut, c.heure_fin, s.num_salle,
                CONCAT(e.nom,' ',e.prenom) AS etudiant, e.filiere,
                GROUP_CONCAT(CONCAT(p.nom,' ',p.prenom,'(',pa.role_jury,')') SEPARATOR ', ') AS jury
            FROM Soutenance s
            JOIN Creneau c    ON s.id_cren = c.id_cren
            JOIN Etudiant e   ON s.id_etud = e.id_etud
            JOIN Participer pa ON s.id_stnc = pa.id_stnc
            JOIN Professeur p  ON pa.id_prof = p.id_prof
            GROUP BY s.id_stnc
            ORDER BY c.date_cren, c.heure_debut
        ";
        $rows = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $rows_html = '';
        foreach ($rows as $i => $r) {
            $rows_html .= "
            <tr>
                <td>" . ($i + 1) . "</td>
                <td>{$r['date_cren']}</td>
                <td>{$r['heure_debut']} - {$r['heure_fin']}</td>
                <td>Salle {$r['num_salle']}</td>
                <td>{$r['etudiant']} <br><small>{$r['filiere']}</small></td>
                <td>{$r['jury']}</td>
            </tr>";
        }

        $html = "
        <!DOCTYPE html><html><head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; font-size: 12px; color: #111; }
            h1 { color: #1a56db; text-align: center; margin-bottom: 4px; }
            .sub { text-align:center; color:#6b7280; margin-bottom:20px; font-size:11px; }
            table { width:100%; border-collapse:collapse; margin-top:10px; }
            th { background:#1a56db; color:#fff; padding:8px; font-size:11px; }
            td { padding:7px 8px; border-bottom:1px solid #e5e7eb; vertical-align:top; }
            tr:nth-child(even) td { background:#f8faff; }
            small { color:#6b7280; }
        </style>
        </head><body>
        <h1>Planning des Soutenances PFE</h1>
        <div class='sub'>Session 2024-2025 — Généré le " . date('d/m/Y H:i') . "</div>
        <table>
            <thead><tr>
                <th>#</th><th>Date</th><th>Horaire</th>
                <th>Salle</th><th>Étudiant</th><th>Jury</th>
            </tr></thead>
            <tbody>$rows_html</tbody>
        </table>
        </body></html>";

        $this->makePDF($html, 'planning_soutenances.pdf');
    }

    // ── PDF Affectation encadrants ──
    public function generateAffectation(): void
    {
        $sql = "
            SELECT 
                CONCAT(p.nom,' ',p.prenom) AS professeur,
                p.specialite,
                GROUP_CONCAT(CONCAT(e.nom,' ',e.prenom,'  (',e.filiere,')') SEPARATOR '\n') AS etudiants,
                COUNT(e.id_etud) AS nb
            FROM Professeur p
            LEFT JOIN Etudiant e ON p.id_prof = e.id_prof
            GROUP BY p.id_prof
            ORDER BY p.nom
        ";
        $rows = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $rows_html = '';
        foreach ($rows as $i => $r) {
            $etuds = nl2br(htmlspecialchars($r['etudiants'] ?? '—'));
            $rows_html .= "
            <tr>
                <td>" . ($i + 1) . "</td>
                <td><strong>{$r['professeur']}</strong><br><small>{$r['specialite']}</small></td>
                <td>{$etuds}</td>
                <td style='text-align:center'><strong>{$r['nb']}</strong></td>
            </tr>";
        }

        $html = "
        <!DOCTYPE html><html><head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; font-size: 12px; }
            h1 { color: #1a56db; text-align:center; }
            .sub { text-align:center; color:#6b7280; font-size:11px; margin-bottom:20px; }
            table { width:100%; border-collapse:collapse; }
            th { background:#0e9f6e; color:#fff; padding:8px; }
            td { padding:7px 8px; border-bottom:1px solid #e5e7eb; vertical-align:top; }
            tr:nth-child(even) td { background:#f0fdf4; }
            small { color:#6b7280; }
        </style>
        </head><body>
        <h1>Affectation des Encadrants</h1>
        <div class='sub'>Session 2024-2025 — Généré le " . date('d/m/Y H:i') . "</div>
        <table>
            <thead><tr>
                <th>#</th><th>Professeur</th><th>Étudiants encadrés</th><th>Nb</th>
            </tr></thead>
            <tbody>$rows_html</tbody>
        </table>
        </body></html>";

        $this->makePDF($html, 'affectation_encadrants.pdf');
    }

    // ── PDF PV de soutenance ──
    public function generatePV(int $idStnc): void
    {
        $sql = "
            SELECT 
                s.id_stnc,
                CONCAT(e.nom,' ',e.prenom) AS etudiant,
                e.filiere, e.sujet_pfe, e.langue_pfe,
                c.date_cren, c.heure_debut, c.heure_fin,
                s.num_salle
            FROM Soutenance s
            JOIN Etudiant e ON s.id_etud = e.id_etud
            JOIN Creneau  c ON s.id_cren  = c.id_cren
            WHERE s.id_stnc = :id
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idStnc]);
        $stnc = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$stnc) die("Soutenance introuvable.");

        $sqlJury = "
            SELECT CONCAT(p.nom,' ',p.prenom) AS nom, pa.role_jury, p.specialite
            FROM Participer pa
            JOIN Professeur p ON pa.id_prof = p.id_prof
            WHERE pa.id_stnc = :id
        ";
        $stmtJ = $this->db->prepare($sqlJury);
        $stmtJ->execute([':id' => $idStnc]);
        $jury  = $stmtJ->fetchAll(PDO::FETCH_ASSOC);

        $jury_html = '';
        foreach ($jury as $j) {
            $jury_html .= "<tr>
                <td>{$j['nom']}</td>
                <td>{$j['specialite']}</td>
                <td>" . ucfirst($j['role_jury']) . "</td>
                <td style='width:120px;border-bottom:1px solid #999'>&nbsp;</td>
            </tr>";
        }

        $html = "
        <!DOCTYPE html><html><head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; font-size: 12px; color:#111; }
            h1 { color:#1a56db; text-align:center; font-size:18px; }
            h2 { color:#1a56db; font-size:13px; border-bottom:2px solid #1a56db; padding-bottom:4px; margin-top:24px; }
            .header-info { text-align:center; color:#6b7280; font-size:11px; margin-bottom:20px; }
            .info-grid { display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:16px; }
            .info-item { background:#f9fafb; border:1px solid #e5e7eb; padding:8px 12px; border-radius:6px; }
            .label { color:#6b7280; font-size:10px; text-transform:uppercase; }
            .value { font-weight:bold; font-size:13px; margin-top:2px; }
            table { width:100%; border-collapse:collapse; margin-top:10px; }
            th { background:#1a56db; color:#fff; padding:7px; text-align:left; }
            td { padding:7px; border-bottom:1px solid #e5e7eb; }
            .signature-section { margin-top:40px; }
            .sig-row { display:flex; justify-content:space-between; margin-top:30px; }
            .sig-box { width:30%; text-align:center; }
            .sig-line { border-top:1px solid #999; margin-top:40px; padding-top:4px; font-size:10px; color:#999; }
            .note-box { border:1px solid #e5e7eb; min-height:60px; margin-top:6px; padding:8px; border-radius:4px; }
        </style>
        </head><body>

        <h1>Procès-Verbal de Soutenance</h1>
        <div class='header-info'>
            École Supérieure — Session PFE 2024-2025<br>
            Document généré le " . date('d/m/Y à H:i') . "
        </div>

        <h2>Informations de la soutenance</h2>
        <table>
            <tr>
                <td><span class='label'>Étudiant</span><br><strong>{$stnc['etudiant']}</strong></td>
                <td><span class='label'>Filière</span><br><strong>{$stnc['filiere']}</strong></td>
            </tr>
            <tr>
                <td><span class='label'>Sujet PFE</span><br><strong>{$stnc['sujet_pfe']}</strong></td>
                <td><span class='label'>Langue</span><br><strong>{$stnc['langue_pfe']}</strong></td>
            </tr>
            <tr>
                <td><span class='label'>Date</span><br><strong>{$stnc['date_cren']}</strong></td>
                <td><span class='label'>Horaire</span><br><strong>{$stnc['heure_debut']} – {$stnc['heure_fin']}</strong></td>
            </tr>
            <tr>
                <td><span class='label'>Salle</span><br><strong>Salle {$stnc['num_salle']}</strong></td>
                <td></td>
            </tr>
        </table>

        <h2>Composition du jury</h2>
        <table>
            <thead><tr>
                <th>Nom & Prénom</th><th>Spécialité</th><th>Rôle</th><th>Signature</th>
            </tr></thead>
            <tbody>$jury_html</tbody>
        </table>

        <h2>Délibération</h2>
        <table>
            <tr>
                <td><span class='label'>Note /20</span></td>
                <td style='border-bottom:1px solid #999;width:80px'>&nbsp;</td>
                <td><span class='label'>Mention</span></td>
                <td style='border-bottom:1px solid #999;width:120px'>&nbsp;</td>
            </tr>
        </table>

        <h2>Observations du jury</h2>
        <div class='note-box'>&nbsp;</div>

        <div class='signature-section'>
            <table style='margin-top:40px'>
                <tr>
                    <td style='text-align:center;width:33%'>
                        Le Président du jury<br><br><br><br>
                        <div style='border-top:1px solid #999;padding-top:4px;font-size:10px;color:#999'>Signature</div>
                    </td>
                    <td style='text-align:center;width:33%'>
                        L'Encadrant<br><br><br><br>
                        <div style='border-top:1px solid #999;padding-top:4px;font-size:10px;color:#999'>Signature</div>
                    </td>
                    <td style='text-align:center;width:33%'>
                        Le Rapporteur<br><br><br><br>
                        <div style='border-top:1px solid #999;padding-top:4px;font-size:10px;color:#999'>Signature</div>
                    </td>
                </tr>
            </table>
        </div>

        </body></html>";

        $this->makePDF($html, "PV_soutenance_{$idStnc}.pdf");
    }
}