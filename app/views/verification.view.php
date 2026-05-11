<!DOCTYPE html>
<html lang="fr">
 
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification - Contraintes PFE</title>
 
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;600;700&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
 
    <style>
        :root {
            --primary:   #1a56db;
            --secondary: #0e9f6e;
            --danger:    #e02424;
            --warning:   #ff8c00;
            --bg:        #f0f4ff;
            --card-bg:   #ffffff;
            --text:      #111928;
            --muted:     #6b7280;
            --border:    #e5e7eb;
            --radius:    14px;
        }
 
        * { box-sizing: border-box; margin: 0; padding: 0; }
 
        body {
            font-family: 'Sora', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
        }
 
        /* ── SIDEBAR ── */
        .sidebar {
            position: fixed;
            top: 0; left: 0;
            width: 240px;
            height: 100vh;
            background: var(--primary);
            display: flex;
            flex-direction: column;
            padding: 2rem 1.2rem;
            z-index: 100;
        }
 
        .sidebar-logo {
            font-size: 1.3rem;
            font-weight: 700;
            color: #fff;
            letter-spacing: -.5px;
            margin-bottom: 2.5rem;
            display: flex;
            align-items: center;
            gap: .6rem;
        }
 
        .sidebar-logo i { font-size: 1.5rem; }
 
        .nav-item-custom {
            display: flex;
            align-items: center;
            gap: .8rem;
            color: rgba(255,255,255,.75);
            text-decoration: none;
            padding: .7rem 1rem;
            border-radius: 10px;
            font-size: .9rem;
            font-weight: 500;
            margin-bottom: .3rem;
            transition: background .2s, color .2s;
        }
 
        .nav-item-custom:hover,
        .nav-item-custom.active {
            background: rgba(255,255,255,.18);
            color: #fff;
        }
 
        .nav-item-custom i { font-size: 1.1rem; }
 
        /* ── MAIN ── */
        .main {
            margin-left: 240px;
            padding: 2rem 2.5rem;
        }
 
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
        }
 
        .page-title { font-size: 1.6rem; font-weight: 700; }
        .page-sub   { font-size: .85rem; color: var(--muted); margin-top: .2rem; }
 
        /* ── KPI CARDS ── */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.2rem;
            margin-bottom: 2rem;
        }
 
        .kpi-card {
            background: var(--card-bg);
            border-radius: var(--radius);
            padding: 1.2rem 1.4rem;
            border: 1px solid var(--border);
            box-shadow: 0 2px 8px rgba(0,0,0,.05);
            display: flex;
            align-items: center;
            gap: 1rem;
        }
 
        .kpi-icon {
            width: 48px; height: 48px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem;
            flex-shrink: 0;
        }
 
        .kpi-icon.red    { background: #fee2e2; color: var(--danger); }
        .kpi-icon.green  { background: #d1fae5; color: var(--secondary); }
        .kpi-icon.orange { background: #fff3cd; color: var(--warning); }
        .kpi-icon.blue   { background: #e8f0ff; color: var(--primary); }
 
        .kpi-label { font-size: .78rem; color: var(--muted); margin-bottom: .2rem; }
        .kpi-value { font-size: 1.7rem; font-weight: 700; font-family: 'JetBrains Mono', monospace; }
 
        /* ── ALERT SUCCESS ── */
        .alert-ok {
            background: #d1fae5;
            border: 1px solid #6ee7b7;
            color: #065f46;
            border-radius: var(--radius);
            padding: 1.2rem 1.5rem;
            display: flex;
            align-items: center;
            gap: .8rem;
            font-weight: 500;
            margin-bottom: 1.5rem;
        }
 
        /* ── ANOMALIES CARDS ── */
        .anomalie-section {
            background: var(--card-bg);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            box-shadow: 0 2px 8px rgba(0,0,0,.05);
            margin-bottom: 1.2rem;
            overflow: hidden;
        }
 
        .anomalie-header {
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--border);
        }
 
        .anomalie-header-title {
            display: flex;
            align-items: center;
            gap: .6rem;
            font-weight: 600;
            font-size: .95rem;
        }
 
        .section-rouge  .anomalie-header { background: #fff5f5; }
        .section-orange .anomalie-header { background: #fffbeb; }
        .section-bleu   .anomalie-header { background: #eff6ff; }
 
        .badge-count {
            font-family: 'JetBrains Mono', monospace;
            font-size: .78rem;
            padding: .25rem .6rem;
            border-radius: 10px;
            font-weight: 700;
        }
 
        .badge-count.rouge  { background: #fee2e2; color: var(--danger); }
        .badge-count.orange { background: #fff3cd; color: var(--warning); }
        .badge-count.bleu   { background: #dbeafe; color: var(--primary); }
        .badge-count.vert   { background: #d1fae5; color: var(--secondary); }
 
        .anomalie-list { padding: 0; list-style: none; }
 
        .anomalie-item {
            display: flex;
            align-items: flex-start;
            gap: .8rem;
            padding: .9rem 1.5rem;
            border-bottom: 1px solid var(--border);
            font-size: .88rem;
        }
 
        .anomalie-item:last-child { border-bottom: none; }
 
        .anomalie-item i {
            font-size: 1rem;
            margin-top: .1rem;
            flex-shrink: 0;
        }
 
        .rouge  .anomalie-item i { color: var(--danger); }
        .orange .anomalie-item i { color: var(--warning); }
        .bleu   .anomalie-item i { color: var(--primary); }
 
        /* ── CHARTS ROW ── */
        .charts-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.2rem;
            margin-bottom: 1.5rem;
        }
 
        .chart-card {
            background: var(--card-bg);
            border-radius: var(--radius);
            padding: 1.5rem;
            border: 1px solid var(--border);
            box-shadow: 0 2px 8px rgba(0,0,0,.05);
        }
 
        .chart-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 1.2rem;
            display: flex;
            align-items: center;
            gap: .5rem;
        }
 
        .chart-title i { color: var(--primary); }
 
        .btn-verifie {
            background: var(--primary);
            color: #fff;
            border: none;
            padding: .55rem 1.3rem;
            border-radius: 10px;
            font-size: .88rem;
            font-family: 'Sora', sans-serif;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: .5rem;
            transition: opacity .2s;
        }
 
        .btn-verifie:hover { opacity: .88; }
    </style>
</head>
 
<body>
 
<?php
    /* ══════════════════════════════════════════
       DONNÉES MOCK — À remplacer par vrai service
       ══════════════════════════════════════════ */
 
    // Anomalies critiques (chevauchements, conflits prof)
    $erreursCritiques = [
        "Chevauchement salle A1 : Prof. Ahmed affecté à 2 soutenances à 09:00 le 2025-06-01",
        "Prof. Sara planifiée dans salle B2 ET C3 en même temps le 2025-06-02 à 14:00",
    ];
 
    // Avertissements (repos insuffisant, encadrement déséquilibré)
    $avertissements = [
        "Prof. Karim : moins d'1h de repos entre soutenance 10:00 et 11:00 le 2025-06-01",
        "Déséquilibre encadrement : Prof. Ali encadre 6 étudiants (moyenne = 3,5)",
        "Prof. Amal : 0 soutenance planifiée — vérifier disponibilité",
    ];
 
    // Infos (vérifications OK)
    $infos = [
        "Contrainte jury informatique : tous les jurys ont au moins 2 profs informatique ✓",
        "Contrainte anglais : PFE rédigées en anglais ont un prof d'anglais dans le jury ✓",
    ];
 
    // Stat encadrement par prof (pour graphe)
    $encadrStats = [
        "Prof. Ahmed"  => 5,
        "Prof. Sara"   => 6,
        "Prof. Karim"  => 4,
        "Prof. Ali"    => 6,
        "Prof. Amal"   => 2,
        "Prof. Youssef"=> 4,
    ];
 
    // Types anomalies (pour pie chart)
    $typesAnomalies = [
        "Chevauchements" => 2,
        "Repos insuffisant" => 1,
        "Déséquilibre encadrement" => 1,
        "Double affectation" => 1,
    ];
 
    $totalErreurs = count($erreursCritiques) + count($avertissements);
    $nbOk         = count($infos);
?>
 
<!-- ── SIDEBAR ── -->
<div class="sidebar">
    <div class="sidebar-logo">
        <i class="bi bi-mortarboard-fill"></i> PFE Manager
    </div>
    <a href="dashboard.view.php" class="nav-item-custom">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="planning.view.php" class="nav-item-custom">
        <i class="bi bi-calendar3"></i> Planning
    </a>
    <a href="verification.view.php" class="nav-item-custom active">
        <i class="bi bi-shield-check"></i> Vérification
    </a>
</div>
 
<!-- ── MAIN ── -->
<div class="main">
 
    <!-- Header -->
    <div class="page-header">
        <div>
            <div class="page-title">Vérification des <span style="color:var(--primary)">contraintes</span></div>
            <div class="page-sub">Contrôle automatique du planning et des affectations</div>
        </div>
        <button class="btn-verifie" onclick="window.location.reload()">
            <i class="bi bi-arrow-repeat"></i> Relancer la vérification
        </button>
    </div>
 
    <!-- KPI Cards -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-icon red"><i class="bi bi-x-octagon-fill"></i></div>
            <div>
                <div class="kpi-label">Erreurs critiques</div>
                <div class="kpi-value"><?= count($erreursCritiques) ?></div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon orange"><i class="bi bi-exclamation-triangle-fill"></i></div>
            <div>
                <div class="kpi-label">Avertissements</div>
                <div class="kpi-value"><?= count($avertissements) ?></div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon green"><i class="bi bi-check-circle-fill"></i></div>
            <div>
                <div class="kpi-label">Contraintes OK</div>
                <div class="kpi-value"><?= $nbOk ?></div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon blue"><i class="bi bi-list-check"></i></div>
            <div>
                <div class="kpi-label">Total vérifications</div>
                <div class="kpi-value"><?= $totalErreurs + $nbOk ?></div>
            </div>
        </div>
    </div>
 
    <!-- Graphiques -->
    <div class="charts-grid">
 
        <div class="chart-card">
            <div class="chart-title">
                <i class="bi bi-pie-chart-fill"></i>
                Types d'anomalies détectées
            </div>
            <canvas id="chartAnomalies" height="160"></canvas>
        </div>
 
        <div class="chart-card">
            <div class="chart-title">
                <i class="bi bi-bar-chart-fill"></i>
                Encadrement par prof (équilibre)
            </div>
            <canvas id="chartEncadr" height="160"></canvas>
        </div>
 
    </div>
 
    <!-- Erreurs critiques -->
    <?php if (!empty($erreursCritiques)): ?>
    <div class="anomalie-section section-rouge">
        <div class="anomalie-header">
            <div class="anomalie-header-title">
                <i class="bi bi-x-octagon-fill" style="color:var(--danger)"></i>
                Erreurs critiques — à corriger immédiatement
            </div>
            <span class="badge-count rouge"><?= count($erreursCritiques) ?></span>
        </div>
        <ul class="anomalie-list rouge">
            <?php foreach ($erreursCritiques as $err): ?>
            <li class="anomalie-item">
                <i class="bi bi-x-circle-fill"></i>
                <?= $err ?>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
 
    <!-- Avertissements -->
    <?php if (!empty($avertissements)): ?>
    <div class="anomalie-section section-orange">
        <div class="anomalie-header">
            <div class="anomalie-header-title">
                <i class="bi bi-exclamation-triangle-fill" style="color:var(--warning)"></i>
                Avertissements — à vérifier
            </div>
            <span class="badge-count orange"><?= count($avertissements) ?></span>
        </div>
        <ul class="anomalie-list orange">
            <?php foreach ($avertissements as $avert): ?>
            <li class="anomalie-item">
                <i class="bi bi-exclamation-circle"></i>
                <?= $avert ?>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
 
    <!-- Contraintes OK -->
    <?php if (!empty($infos)): ?>
    <div class="anomalie-section section-bleu">
        <div class="anomalie-header">
            <div class="anomalie-header-title">
                <i class="bi bi-check-circle-fill" style="color:var(--secondary)"></i>
                Contraintes vérifiées avec succès
            </div>
            <span class="badge-count vert"><?= count($infos) ?></span>
        </div>
        <ul class="anomalie-list bleu">
            <?php foreach ($infos as $info): ?>
            <li class="anomalie-item">
                <i class="bi bi-check2-circle" style="color:var(--secondary) !important"></i>
                <?= $info ?>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
 
    <!-- Aucune anomalie -->
    <?php if ($totalErreurs === 0): ?>
    <div class="alert-ok">
        <i class="bi bi-shield-fill-check" style="font-size:1.5rem"></i>
        <div>
            <strong>Aucune anomalie détectée.</strong><br>
            <span style="font-size:.88rem">Toutes les contraintes sont respectées.</span>
        </div>
    </div>
    <?php endif; ?>
 
</div><!-- /main -->
 
<script>
    /* 1. Pie — types anomalies */
    new Chart(document.getElementById('chartAnomalies'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_keys($typesAnomalies)) ?>,
            datasets: [{
                data: <?= json_encode(array_values($typesAnomalies)) ?>,
                backgroundColor: ['#e02424','#ff8c00','#1a56db','#7e3af2'],
                borderWidth: 0,
                hoverOffset: 6,
            }]
        },
        options: {
            cutout: '60%',
            responsive: true,
            plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } }
        }
    });
 
    /* 2. Bar — encadrement */
    const avg = <?= array_sum($encadrStats) / count($encadrStats) ?>;
    const vals = <?= json_encode(array_values($encadrStats)) ?>;
    const bgColors = vals.map(v => v > avg + 1 || v < avg - 1 ? '#e02424' : '#0e9f6e');
 
    new Chart(document.getElementById('chartEncadr'), {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_keys($encadrStats)) ?>,
            datasets: [
                {
                    label: 'Étudiants encadrés',
                    data: vals,
                    backgroundColor: bgColors,
                    borderRadius: 7,
                    borderSkipped: false,
                },
                {
                    type: 'line',
                    label: 'Moyenne',
                    data: Array(vals.length).fill(avg),
                    borderColor: '#1a56db',
                    borderDash: [6, 4],
                    pointRadius: 0,
                    borderWidth: 2,
                }
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } },
            scales: {
                y: { beginAtZero: true, grid: { color: '#f0f0f0' } },
                x: { grid: { display: false }, ticks: { font: { size: 10 } } }
            }
        }
    });
</script>
 
</body>
</html>