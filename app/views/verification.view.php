<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification — Contraintes PFE</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        body { font-family:'Plus Jakarta Sans',sans-serif; background:var(--bg); color:var(--text); font-size:14px; }

        /* ── Sidebar ── */
        .sidebar {
            position:fixed; top:0; left:0;
            width:var(--sidebar-w); height:100vh;
            background:#0f172a; display:flex; flex-direction:column;
            z-index:100; overflow-y:auto;
        }
        .sidebar-brand { padding:20px 20px 16px; border-bottom:1px solid rgba(255,255,255,.08); }
        .sidebar-brand .brand-name { font-size:15px; font-weight:700; color:#fff; }
        .sidebar-brand .brand-sub  { font-size:10px; color:#94a3b8; margin-top:2px; }
        .sidebar-section { padding:20px 12px 8px; font-size:10px; font-weight:600; color:#475569; text-transform:uppercase; letter-spacing:.08em; }
        .nav-link-item {
            display:flex; align-items:center; gap:10px;
            padding:9px 12px; margin:1px 8px; border-radius:8px;
            color:#94a3b8; text-decoration:none; font-size:13px; font-weight:500; transition:all .15s;
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

        /* ── Bouton relancer ── */
        .btn-relancer {
            background:var(--blue); color:#fff;
            border:none; border-radius:8px;
            padding:8px 18px; font-size:13px; font-weight:600;
            cursor:pointer; display:flex; align-items:center; gap:7px;
            transition:opacity .15s; text-decoration:none;
        }
        .btn-relancer:hover { opacity:.88; color:#fff; }

        /* ── KPI Summary (4 cartes simples) ── */
        .kpi-row {
            display:grid; grid-template-columns:repeat(4,1fr);
            gap:12px; margin-bottom:22px;
        }
        @media(max-width:900px){ .kpi-row { grid-template-columns:repeat(2,1fr); } }
        .kpi-box {
            background:var(--white); border:1px solid var(--border);
            border-radius:10px; padding:16px 18px;
            display:flex; align-items:center; gap:14px;
        }
        .kpi-box-icon {
            width:40px; height:40px; border-radius:9px;
            display:flex; align-items:center; justify-content:center;
            font-size:18px; flex-shrink:0;
        }
        .kib-red    { background:var(--red-lt); color:var(--red); }
        .kib-amber  { background:var(--amber-lt); color:var(--amber); }
        .kib-green  { background:var(--green-lt); color:var(--green); }
        .kib-blue   { background:var(--blue-lt); color:var(--blue); }
        .kpi-box-val { font-size:26px; font-weight:700; line-height:1; letter-spacing:-.03em; }
        .kpi-box-lbl { font-size:11px; color:var(--slate); margin-top:3px; }

        /* ── Alert "tout OK" ── */
        .all-ok {
            background:var(--green-lt); border:1px solid #bbf7d0;
            border-radius:10px; padding:14px 18px;
            display:flex; align-items:center; gap:10px;
            color:var(--green); font-weight:600; font-size:13px;
            margin-bottom:18px;
        }
        .all-ok i { font-size:20px; }

        /* ══ LAYOUT SPLIT : liste gauche + mini graphique droite ══ */
        .split-layout {
            display:grid; grid-template-columns:1fr 280px;
            gap:14px; align-items:start;
        }
        @media(max-width:1000px){ .split-layout { grid-template-columns:1fr; } }

        /* ── Sections d'anomalies ── */
        .anomaly-section {
            border:1px solid var(--border); border-radius:10px;
            overflow:hidden; margin-bottom:14px; background:var(--white);
        }
        .anomaly-header {
            padding:12px 16px;
            display:flex; align-items:center; justify-content:space-between;
            border-bottom:1px solid var(--border);
        }
        .anomaly-header-left {
            display:flex; align-items:center; gap:8px;
            font-size:13px; font-weight:600;
        }
        .anom-badge {
            font-size:11px; font-weight:700;
            padding:3px 9px; border-radius:20px;
        }
        .anom-badge.red    { background:var(--red-lt); color:var(--red); }
        .anom-badge.amber  { background:var(--amber-lt); color:var(--amber); }
        .anom-badge.green  { background:var(--green-lt); color:var(--green); }

        /* couleurs des sections */
        .sec-red   .anomaly-header { background:#fff8f8; }
        .sec-amber .anomaly-header { background:#fffdf5; }
        .sec-green .anomaly-header { background:#f7fdf9; }

        .anomaly-list { list-style:none; }
        .anomaly-item {
            display:flex; align-items:flex-start; gap:10px;
            padding:10px 16px; border-bottom:1px solid #f8fafc;
            font-size:12.5px; line-height:1.5;
        }
        .anomaly-item:last-child { border-bottom:none; }
        .anomaly-item .ai-icon { font-size:14px; flex-shrink:0; margin-top:2px; }
        .ai-icon.red   { color:var(--red); }
        .ai-icon.amber { color:var(--amber); }
        .ai-icon.green { color:var(--green); }

        /* ── Carte graphique ── */
        .chart-card {
            background:var(--white); border:1px solid var(--border);
            border-radius:10px; padding:16px; position:sticky; top:24px;
        }
        .chart-card-title {
            font-size:12px; font-weight:600; color:var(--slate);
            text-transform:uppercase; letter-spacing:.06em;
            margin-bottom:14px; display:flex; align-items:center; gap:6px;
        }
        .chart-card-title i { color:var(--blue); }
        .no-anomaly-chart {
            text-align:center; padding:32px 16px;
            color:var(--green); font-size:12px;
        }
        .no-anomaly-chart i { font-size:36px; display:block; margin-bottom:8px; }

        /* ── Encadrement mini-table ── */
        .encadr-card {
            background:var(--white); border:1px solid var(--border);
            border-radius:10px; overflow:hidden; margin-top:14px;
        }
        .encadr-header {
            padding:12px 16px; border-bottom:1px solid var(--border);
            font-size:12px; font-weight:600; color:var(--slate);
            text-transform:uppercase; letter-spacing:.06em;
            display:flex; align-items:center; gap:6px;
        }
        .encadr-header i { color:var(--blue); }
        .encadr-row {
            display:flex; align-items:center; gap:10px;
            padding:8px 14px; border-bottom:1px solid #f8fafc;
            font-size:12px;
        }
        .encadr-row:last-child { border-bottom:none; }
        .encadr-name { flex:1; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .encadr-bar-wrap { width:70px; height:5px; background:#f1f5f9; border-radius:3px; overflow:hidden; }
        .encadr-bar-fill { height:100%; border-radius:3px; transition:width .4s; }
        .encadr-nb { font-size:12px; font-weight:600; width:20px; text-align:right; }
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

// Instancier le service via MVC
// En vue standalone, on instancie directement pour compatibilité
require_once __DIR__ . '/../app/Services/VerificationSvc.php';
require_once __DIR__ . '/../app/Utils/ConstraintChecker.php';
require_once __DIR__ . '/../app/Models/Model.php';
require_once __DIR__ . '/../app/Models/Professeur.php';

use App\Services\VerificationSvc;

$service = new VerificationSvc();
$rapport = $service->getAnomalies();

$erreursCritiques = $rapport['errors'];
$avertissements   = $rapport['warnings'];
$infos            = $rapport['success'];
$encadrStats      = $rapport['encadrStats'];
$typesAnomalies   = $rapport['typesAnomalies'];

$totalErreurs = count($erreursCritiques) + count($avertissements);
$nbOk         = count($infos);

// Stats encadrement pour le graphique
$maxEncadr = !empty($encadrStats) ? max(array_values($encadrStats)) : 1;
$avgEncadr = !empty($encadrStats) ? array_sum($encadrStats)/count($encadrStats) : 0;
?>

<!-- ══ Sidebar ═════════════════════════════════════════ -->
<div class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-name"><i class="bi bi-mortarboard-fill" style="color:#60a5fa"></i> PFE Manager</div>
        <div class="brand-sub">Session 2024-2025</div>
    </div>
    <div class="sidebar-section">Navigation</div>
    <a href="dashboard.view.php"    class="nav-link-item"><i class="bi bi-grid-1x2"></i> Dashboard</a>
    <a href="planning.view.php"     class="nav-link-item"><i class="bi bi-calendar3"></i> Planning</a>
    <a href="verification.view.php" class="nav-link-item active"><i class="bi bi-shield-check"></i> Vérification</a>
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
            <div class="page-title">Vérification des <span>contraintes</span></div>
            <div class="page-meta">Contrôle automatique du planning et des affectations</div>
        </div>
        <button class="btn-relancer" onclick="window.location.reload()">
            <i class="bi bi-arrow-repeat"></i> Relancer la vérification
        </button>
    </div>

    <!-- KPI Summary -->
    <div class="kpi-row">
        <div class="kpi-box">
            <div class="kpi-box-icon kib-red"><i class="bi bi-x-octagon-fill"></i></div>
            <div>
                <div class="kpi-box-val" style="color:var(--red)"><?= count($erreursCritiques) ?></div>
                <div class="kpi-box-lbl">Erreurs critiques</div>
            </div>
        </div>
        <div class="kpi-box">
            <div class="kpi-box-icon kib-amber"><i class="bi bi-exclamation-triangle-fill"></i></div>
            <div>
                <div class="kpi-box-val" style="color:var(--amber)"><?= count($avertissements) ?></div>
                <div class="kpi-box-lbl">Avertissements</div>
            </div>
        </div>
        <div class="kpi-box">
            <div class="kpi-box-icon kib-green"><i class="bi bi-check-circle-fill"></i></div>
            <div>
                <div class="kpi-box-val" style="color:var(--green)"><?= $nbOk ?></div>
                <div class="kpi-box-lbl">Contraintes OK</div>
            </div>
        </div>
        <div class="kpi-box">
            <div class="kpi-box-icon kib-blue"><i class="bi bi-list-check"></i></div>
            <div>
                <div class="kpi-box-val"><?= $totalErreurs + $nbOk ?></div>
                <div class="kpi-box-lbl">Total vérifications</div>
            </div>
        </div>
    </div>

    <?php if ($totalErreurs === 0): ?>
    <div class="all-ok">
        <i class="bi bi-shield-fill-check"></i>
        <div>
            <strong>Aucune anomalie détectée.</strong>
            <span style="font-weight:400;margin-left:6px">Toutes les contraintes sont respectées.</span>
        </div>
    </div>
    <?php endif; ?>

    <!-- Layout split -->
    <div class="split-layout">

        <!-- Colonne gauche : listes anomalies -->
        <div>

            <!-- Erreurs critiques -->
            <?php if (!empty($erreursCritiques)): ?>
            <div class="anomaly-section sec-red">
                <div class="anomaly-header">
                    <div class="anomaly-header-left">
                        <i class="bi bi-x-octagon-fill" style="color:var(--red)"></i>
                        Erreurs critiques — à corriger immédiatement
                    </div>
                    <span class="anom-badge red"><?= count($erreursCritiques) ?></span>
                </div>
                <ul class="anomaly-list">
                    <?php foreach ($erreursCritiques as $err): ?>
                    <li class="anomaly-item">
                        <i class="bi bi-x-circle-fill ai-icon red"></i>
                        <span><?= htmlspecialchars($err['message']) ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <!-- Avertissements -->
            <?php if (!empty($avertissements)): ?>
            <div class="anomaly-section sec-amber">
                <div class="anomaly-header">
                    <div class="anomaly-header-left">
                        <i class="bi bi-exclamation-triangle-fill" style="color:var(--amber)"></i>
                        Avertissements — à vérifier
                    </div>
                    <span class="anom-badge amber"><?= count($avertissements) ?></span>
                </div>
                <ul class="anomaly-list">
                    <?php foreach ($avertissements as $w): ?>
                    <li class="anomaly-item">
                        <i class="bi bi-exclamation-circle-fill ai-icon amber"></i>
                        <span><?= htmlspecialchars($w['message']) ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <!-- Contraintes OK -->
            <?php if (!empty($infos)): ?>
            <div class="anomaly-section sec-green">
                <div class="anomaly-header">
                    <div class="anomaly-header-left">
                        <i class="bi bi-check-circle-fill" style="color:var(--green)"></i>
                        Contraintes validées avec succès
                    </div>
                    <span class="anom-badge green"><?= count($infos) ?></span>
                </div>
                <ul class="anomaly-list">
                    <?php foreach ($infos as $info): ?>
                    <li class="anomaly-item">
                        <i class="bi bi-check2-circle ai-icon green"></i>
                        <span><?= htmlspecialchars($info['message']) ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

        </div><!-- /colonne gauche -->

        <!-- Colonne droite : graphique + stats encadrement -->
        <div>

            <!-- Graphique anomalies (unique, petit) -->
            <div class="chart-card">
                <div class="chart-card-title"><i class="bi bi-pie-chart"></i> Types d'anomalies</div>
                <?php if (!empty($typesAnomalies)): ?>
                <canvas id="chartAnomalies" height="200"></canvas>
                <?php else: ?>
                <div class="no-anomaly-chart">
                    <i class="bi bi-shield-check"></i>
                    Aucune anomalie
                </div>
                <?php endif; ?>
            </div>

            <!-- Mini-table encadrement -->
            <?php if (!empty($encadrStats)): ?>
            <div class="encadr-card">
                <div class="encadr-header"><i class="bi bi-people"></i> Encadrement par prof</div>
                <?php foreach ($encadrStats as $prof => $nb):
                    $pct = $maxEncadr > 0 ? round($nb / $maxEncadr * 100) : 0;
                    $color = ($nb > $avgEncadr + 1.5) ? 'var(--red)' : 'var(--green)';
                ?>
                <div class="encadr-row">
                    <div class="encadr-name" title="<?= htmlspecialchars($prof) ?>">
                        <?= htmlspecialchars(mb_substr($prof, 0, 18)) ?><?= mb_strlen($prof) > 18 ? '…' : '' ?>
                    </div>
                    <div class="encadr-bar-wrap">
                        <div class="encadr-bar-fill" style="width:<?= $pct ?>%;background:<?= $color ?>"></div>
                    </div>
                    <div class="encadr-nb" style="color:<?= $color ?>"><?= $nb ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

        </div><!-- /colonne droite -->

    </div><!-- /split-layout -->

</div><!-- /main -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
/* ══ Graphique camembert anomalies ═════════════════════ */
<?php if (!empty($typesAnomalies)): ?>
const ctxA = document.getElementById('chartAnomalies').getContext('2d');
new Chart(ctxA, {
    type: 'doughnut',
    data: {
        labels:   <?= json_encode(array_keys($typesAnomalies)) ?>,
        datasets: [{
            data:            <?= json_encode(array_values($typesAnomalies)) ?>,
            backgroundColor: ['#dc2626','#d97706','#2563eb','#7c3aed','#0d9488'],
            borderWidth:     2,
            borderColor:     '#fff',
            hoverOffset:     6,
        }]
    },
    options: {
        cutout:     '58%',
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    font: { family: "'Plus Jakarta Sans', sans-serif", size: 11 },
                    boxWidth: 10, padding: 12,
                }
            }
        }
    }
});
<?php endif; ?>
</script>
</body>
</html>