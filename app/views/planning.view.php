<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planning — Soutenances PFE</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --blue:    #2563eb;
            --blue-lt: #eff6ff;
            --green:   #16a34a;
            --green-lt:#f0fdf4;
            --amber:   #d97706;
            --amber-lt:#fffbeb;
            --red:     #dc2626;
            --red-lt:  #fef2f2;
            --slate:   #64748b;
            --border:  #e2e8f0;
            --bg:      #f8fafc;
            --white:   #ffffff;
            --text:    #0f172a;
            --sidebar-w: 220px;
        }
        * { box-sizing:border-box; margin:0; padding:0; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg); color: var(--text); font-size:14px;
        }

        /* ── Sidebar ── */
        .sidebar {
            position:fixed; top:0; left:0;
            width:var(--sidebar-w); height:100vh;
            background:#0f172a;
            display:flex; flex-direction:column;
            z-index:100; overflow-y:auto;
        }
        .sidebar-brand { padding:20px 20px 16px; border-bottom:1px solid rgba(255,255,255,.08); }
        .sidebar-brand .brand-name { font-size:15px; font-weight:700; color:#fff; }
        .sidebar-brand .brand-sub  { font-size:10px; color:#94a3b8; margin-top:2px; }
        .sidebar-section { padding:20px 12px 8px; font-size:10px; font-weight:600; color:#475569; text-transform:uppercase; letter-spacing:.08em; }
        .nav-link-item {
            display:flex; align-items:center; gap:10px;
            padding:9px 12px; margin:1px 8px; border-radius:8px;
            color:#94a3b8; text-decoration:none; font-size:13px; font-weight:500;
            transition:all .15s;
        }
        .nav-link-item:hover { background:rgba(255,255,255,.07); color:#e2e8f0; }
        .nav-link-item.active { background:var(--blue); color:#fff; }
        .nav-link-item i { font-size:15px; width:18px; text-align:center; }
        .sidebar-footer { margin-top:auto; padding:16px 20px; border-top:1px solid rgba(255,255,255,.07); font-size:10px; color:#475569; }

        /* ── Main ── */
        .main { margin-left:var(--sidebar-w); min-height:100vh; padding:28px 32px; }

        /* ── Header ── */
        .page-header { margin-bottom:24px; display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:12px; }
        .page-title { font-size:22px; font-weight:700; letter-spacing:-.03em; }
        .page-title span { color:var(--blue); }
        .page-meta { font-size:12px; color:var(--slate); margin-top:4px; }

        /* ── Filtres ── */
        .filter-bar {
            background:var(--white); border:1px solid var(--border);
            border-radius:12px; padding:14px 18px;
            display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap;
            margin-bottom:18px;
        }
        .filter-group { display:flex; flex-direction:column; gap:5px; }
        .filter-group label { font-size:11px; font-weight:600; color:var(--slate); text-transform:uppercase; letter-spacing:.05em; }
        .filter-group select,
        .filter-group input {
            border:1px solid var(--border); border-radius:8px;
            padding:7px 10px; font-size:13px;
            font-family:'Plus Jakarta Sans',sans-serif;
            color:var(--text); background:var(--white);
            outline:none; transition:border .15s;
            min-width:140px;
        }
        .filter-group select:focus,
        .filter-group input:focus { border-color:var(--blue); }
        .btn-reset {
            background:var(--blue-lt); color:var(--blue);
            border:1px solid #bfdbfe; border-radius:8px;
            padding:7px 14px; font-size:13px; font-weight:600;
            cursor:pointer; transition:all .15s;
            display:flex; align-items:center; gap:6px;
        }
        .btn-reset:hover { background:var(--blue); color:#fff; }
        .btn-pdf {
            background:var(--blue); color:#fff;
            border:none; border-radius:8px;
            padding:7px 16px; font-size:13px; font-weight:600;
            cursor:pointer; transition:all .15s;
            display:flex; align-items:center; gap:6px;
            text-decoration:none; margin-left:auto;
        }
        .btn-pdf:hover { opacity:.88; color:#fff; }

        /* ── Table card ── */
        .table-card {
            background:var(--white); border:1px solid var(--border);
            border-radius:12px; overflow:hidden;
        }
        .table-card-header {
            padding:14px 20px; border-bottom:1px solid var(--border);
            display:flex; align-items:center; justify-content:space-between;
        }
        .table-card-title { font-size:13px; font-weight:600; display:flex; align-items:center; gap:7px; }
        .table-card-title i { color:var(--blue); }
        .count-pill {
            background:var(--blue-lt); color:var(--blue);
            font-size:11px; font-weight:600;
            padding:3px 10px; border-radius:20px;
        }

        /* ── Tableau ── */
        .tbl { width:100%; border-collapse:collapse; }
        .tbl thead tr { background:#f8fafc; }
        .tbl th {
            font-size:11px; font-weight:600; color:var(--slate);
            text-transform:uppercase; letter-spacing:.05em;
            padding:10px 14px; border-bottom:1px solid var(--border);
            text-align:left; white-space:nowrap;
        }
        .tbl td { padding:11px 14px; border-bottom:1px solid #f1f5f9; font-size:13px; vertical-align:middle; }
        .tbl tbody tr:last-child td { border-bottom:none; }
        .tbl tbody tr:hover td { background:#fafbff; }
        .hidden { display:none !important; }

        /* ── Badges ── */
        .badge-salle {
            background:var(--blue-lt); color:var(--blue);
            padding:3px 10px; border-radius:6px;
            font-size:12px; font-weight:600; white-space:nowrap;
        }
        .badge-heure {
            background:#f0fdf4; color:var(--green);
            padding:3px 10px; border-radius:6px;
            font-size:12px; font-weight:600;
            font-variant-numeric: tabular-nums;
            white-space:nowrap;
        }
        .jury-item {
            display:flex; align-items:center; gap:6px;
            font-size:12px; margin-bottom:3px;
        }
        .jury-item:last-child { margin-bottom:0; }
        .role-tag {
            font-size:10px; font-weight:600;
            padding:2px 7px; border-radius:10px;
            white-space:nowrap;
        }
        .role-enc   { background:var(--blue-lt); color:var(--blue); }
        .role-pres  { background:var(--green-lt); color:var(--green); }
        .role-rapp  { background:var(--amber-lt); color:var(--amber); }
        .role-jury  { background:#f1f5f9; color:var(--slate); }
        .etud-name  { font-weight:600; font-size:13px; }
        .etud-fil   { font-size:11px; color:var(--slate); margin-top:2px; }
        .no-data {
            text-align:center; padding:48px 24px;
            color:var(--slate); font-size:13px;
        }
        .no-data i { font-size:32px; margin-bottom:8px; display:block; }
    </style>
</head>
<body>

<?php
/* ══ Données ══════════════════════════════════════════ */
try {
    $db = new PDO(
        "mysql:host=localhost;dbname=gestion_pfe;charset=utf8",
        "root", "",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (Exception $e) { die("Erreur DB : " . $e->getMessage()); }

$sql = "
    SELECT
        s.id_stnc,
        c.date_cren   AS date,
        c.heure_debut AS heure,
        c.heure_fin,
        s.num_salle   AS salle,
        CONCAT(e.nom,' ',e.prenom) AS etudiant,
        e.filiere,
        GROUP_CONCAT(
            CONCAT(pr.nom,' ',pr.prenom,'::',pa.role_jury)
            ORDER BY pa.role_jury SEPARATOR '|'
        ) AS jury_raw
    FROM   Soutenance  s
    JOIN   Creneau     c  ON s.id_cren  = c.id_cren
    JOIN   Etudiant    e  ON s.id_etud  = e.id_etud
    LEFT   JOIN Participer  pa ON s.id_stnc  = pa.id_stnc
    LEFT   JOIN Professeur  pr ON pa.id_prof = pr.id_prof
    GROUP  BY s.id_stnc, c.date_cren, c.heure_debut, c.heure_fin,
              s.num_salle, e.nom, e.prenom, e.filiere
    ORDER  BY c.date_cren, c.heure_debut, s.num_salle
";
$rows    = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
$nbTotal = count($rows);

// Construire le planning
$planning = [];
foreach ($rows as $r) {
    $jury = [];
    if ($r['jury_raw']) {
        foreach (explode('|', $r['jury_raw']) as $item) {
            $parts = explode('::', $item, 2);
            $nom   = $parts[0] ?? '';
            $role  = strtolower($parts[1] ?? '');
            $classeMap = [
                'encadrant'  => 'role-enc',
                'président'  => 'role-pres',
                'rapporteur' => 'role-rapp',
                'jury'       => 'role-jury',
            ];
            $jury[] = [
                'nom'   => $nom,
                'role'  => ucfirst($role),
                'classe'=> $classeMap[$role] ?? 'role-jury',
            ];
        }
    }
    $planning[] = [
        'id'      => $r['id_stnc'],
        'date'    => $r['date'],
        'heure'   => substr($r['heure'],0,5).' – '.substr($r['heure_fin'],0,5),
        'salle'   => $r['salle'],
        'etudiant'=> $r['etudiant'],
        'filiere' => $r['filiere'],
        'jury'    => $jury,
    ];
}

$dates  = array_unique(array_column($planning,'date'));
$salles = array_unique(array_column($planning,'salle'));
sort($dates); sort($salles);
?>

<!-- ══ Sidebar ═════════════════════════════════════════ -->
<div class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-name"><i class="bi bi-mortarboard-fill" style="color:#60a5fa"></i> PFE Manager</div>
        <div class="brand-sub">Session 2024-2025</div>
    </div>
    <div class="sidebar-section">Navigation</div>
    <a href="dashboard.view.php"    class="nav-link-item"><i class="bi bi-grid-1x2"></i> Dashboard</a>
    <a href="planning.view.php"     class="nav-link-item active"><i class="bi bi-calendar3"></i> Planning
        <span style="margin-left:auto;background:#2563eb;color:#fff;font-size:10px;padding:1px 7px;border-radius:10px"><?= $nbTotal ?></span>
    </a>
    <a href="verification.view.php" class="nav-link-item"><i class="bi bi-shield-check"></i> Vérification</a>
    <div class="sidebar-section">Actions</div>
    <a href="?action=import"          class="nav-link-item"><i class="bi bi-upload"></i> Importer Excel</a>
    <a href="?action=affectation"     class="nav-link-item"><i class="bi bi-person-lines-fill"></i> Affectation</a>
    <a href="?action=pdf_planning"    class="nav-link-item"><i class="bi bi-file-earmark-pdf"></i> PDF Planning</a>
    <a href="?action=pdf_affectation" class="nav-link-item"><i class="bi bi-file-earmark-pdf"></i> PDF Affectation</a>
    <div class="sidebar-footer">Gestion PFE © 2025<br>Session 2024-2025</div>
</div>

<!-- ══ Main ════════════════════════════════════════════ -->
<div class="main">

    <!-- Header -->
    <div class="page-header">
        <div>
            <div class="page-title">Planning des <span>soutenances</span></div>
            <div class="page-meta">Calendrier complet — Session 2024-2025</div>
        </div>
        <a href="?action=pdf_planning" class="btn-pdf">
            <i class="bi bi-file-earmark-pdf"></i> Exporter PDF
        </a>
    </div>

    <!-- Filtres -->
    <div class="filter-bar">
        <div class="filter-group">
            <label>Date</label>
            <select id="filtreDate">
                <option value="">Toutes les dates</option>
                <?php foreach ($dates as $d): ?>
                <option value="<?= $d ?>"><?= date('d/m/Y', strtotime($d)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <label>Salle</label>
            <select id="filtreSalle">
                <option value="">Toutes les salles</option>
                <?php foreach ($salles as $s): ?>
                <option value="<?= $s ?>">Salle <?= $s ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <label>Étudiant</label>
            <input type="text" id="rechercheEtud" placeholder="Rechercher...">
        </div>
        <button class="btn-reset" onclick="resetFiltres()">
            <i class="bi bi-x-lg"></i> Réinitialiser
        </button>
    </div>

    <!-- Tableau -->
    <div class="table-card">
        <div class="table-card-header">
            <div class="table-card-title">
                <i class="bi bi-calendar3"></i>
                Tableau du planning
            </div>
            <span class="count-pill" id="nbLignes"><?= $nbTotal ?> soutenance(s)</span>
        </div>

        <?php if (empty($planning)): ?>
        <div class="no-data">
            <i class="bi bi-calendar-x"></i>
            Aucune soutenance planifiée pour le moment.
        </div>
        <?php else: ?>
        <div style="overflow-x:auto">
            <table class="tbl" id="tablePlanning">
                <thead>
                    <tr>
                        <th style="width:38px">#</th>
                        <th>Date</th>
                        <th>Horaire</th>
                        <th>Salle</th>
                        <th>Étudiant</th>
                        <th>Jury</th>
                        <th style="width:60px">PV</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($planning as $i => $p): ?>
                <tr data-date="<?= $p['date'] ?>"
                    data-salle="<?= $p['salle'] ?>"
                    data-etud="<?= strtolower($p['etudiant']) ?>">
                    <td style="color:var(--slate);font-size:11px;font-variant-numeric:tabular-nums">
                        <?= str_pad($i+1,2,'0',STR_PAD_LEFT) ?>
                    </td>
                    <td style="white-space:nowrap;font-size:12px;color:var(--slate)">
                        <?= date('d/m/Y', strtotime($p['date'])) ?>
                    </td>
                    <td><span class="badge-heure"><?= $p['heure'] ?></span></td>
                    <td><span class="badge-salle">Salle <?= $p['salle'] ?></span></td>
                    <td>
                        <div class="etud-name"><?= htmlspecialchars($p['etudiant']) ?></div>
                        <?php if ($p['filiere']): ?>
                        <div class="etud-fil"><?= htmlspecialchars($p['filiere']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!empty($p['jury'])): ?>
                        <div>
                        <?php foreach ($p['jury'] as $j): ?>
                            <div class="jury-item">
                                <span class="role-tag <?= $j['classe'] ?>"><?= $j['role'] ?></span>
                                <span><?= htmlspecialchars($j['nom']) ?></span>
                            </div>
                        <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <span style="color:var(--slate);font-size:11px">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="?action=pdf_pv&id=<?= $p['id'] ?>"
                           title="Télécharger le PV"
                           style="color:var(--red);font-size:16px;text-decoration:none">
                            <i class="bi bi-file-earmark-pdf"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

</div><!-- /main -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
/* ══ Filtrage côté client ══════════════════════════════ */
function filtrer() {
    const date  = document.getElementById('filtreDate').value;
    const salle = document.getElementById('filtreSalle').value;
    const etud  = document.getElementById('rechercheEtud').value.toLowerCase().trim();
    const rows  = document.querySelectorAll('#tablePlanning tbody tr');
    let count   = 0;

    rows.forEach(row => {
        const okDate  = !date  || row.dataset.date  === date;
        const okSalle = !salle || row.dataset.salle === salle;
        const okEtud  = !etud  || row.dataset.etud.includes(etud);
        const show    = okDate && okSalle && okEtud;
        row.classList.toggle('hidden', !show);
        if (show) count++;
    });

    document.getElementById('nbLignes').textContent = count + ' soutenance(s)';
}

function resetFiltres() {
    document.getElementById('filtreDate').value    = '';
    document.getElementById('filtreSalle').value   = '';
    document.getElementById('rechercheEtud').value = '';
    filtrer();
}

document.getElementById('filtreDate').addEventListener('change', filtrer);
document.getElementById('filtreSalle').addEventListener('change', filtrer);
document.getElementById('rechercheEtud').addEventListener('input', filtrer);
</script>
</body>
</html>