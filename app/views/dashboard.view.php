<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Gestion PFE</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;600;700&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Chart.js -->
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

        /* ── HEADER ── */
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
        }

        .page-title { font-size: 1.6rem; font-weight: 700; }
        .page-sub   { font-size: .85rem; color: var(--muted); margin-top: .2rem; }

        .badge-session {
            background: var(--primary);
            color: #fff;
            padding: .45rem 1rem;
            border-radius: 20px;
            font-size: .8rem;
            font-family: 'JetBrains Mono', monospace;
        }

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
            padding: 1.4rem 1.6rem;
            border: 1px solid var(--border);
            box-shadow: 0 2px 8px rgba(0,0,0,.05);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .kpi-icon {
            width: 50px; height: 50px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem;
            flex-shrink: 0;
        }

        .kpi-icon.blue   { background: #e8f0ff; color: var(--primary); }
        .kpi-icon.green  { background: #d1fae5; color: var(--secondary); }
        .kpi-icon.red    { background: #fee2e2; color: var(--danger); }
        .kpi-icon.orange { background: #fff3cd; color: var(--warning); }

        .kpi-label { font-size: .8rem; color: var(--muted); margin-bottom: .3rem; }
        .kpi-value { font-size: 1.8rem; font-weight: 700; font-family: 'JetBrains Mono', monospace; }

        /* ── CHARTS GRID ── */
        .charts-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.2rem;
            margin-bottom: 1.2rem;
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

        /* ── TABLE CARD ── */
        .table-card {
            background: var(--card-bg);
            border-radius: var(--radius);
            padding: 1.5rem;
            border: 1px solid var(--border);
            box-shadow: 0 2px 8px rgba(0,0,0,.05);
        }

        .table th {
            font-size: .78rem;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--muted);
            font-weight: 600;
            border-bottom: 2px solid var(--border);
        }

        .table td { vertical-align: middle; font-size: .9rem; }

        .badge-filiere {
            padding: .3rem .7rem;
            border-radius: 20px;
            font-size: .75rem;
            font-weight: 600;
        }

        .badge-info   { background: #dbeafe; color: #1d4ed8; }
        .badge-math   { background: #d1fae5; color: #065f46; }
        .badge-langue { background: #fef3c7; color: #92400e; }

        .progress-bar-custom {
            height: 6px;
            background: var(--border);
            border-radius: 3px;
            overflow: hidden;
            margin-top: .3rem;
        }

        .progress-fill {
            height: 100%;
            border-radius: 3px;
            background: var(--primary);
        }

        @media (max-width: 992px) {
            .sidebar { width: 200px; }
            .main    { margin-left: 200px; }
            .kpi-grid { grid-template-columns: repeat(2, 1fr); }
            .charts-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>

<body>

<?php
    /* ══════════════════════════════════════════
       DONNÉES MOCK — À remplacer par DB plus tard
       ══════════════════════════════════════════ */
    $nbEtudiants   = 120;
    $nbProfs       = 22;
    $nbSoutenances = 77;
    $nbJours       = 3;

    // Répartition par filière
    $filieres = [
        ["nom" => "Ingénierie des Données",     "code" => "ID",   "nb" => 39, "badge" => "badge-info"],
        ["nom" => "Transformation Digitale & IA","code" => "TDAI", "nb" => 26, "badge" => "badge-math"],
        ["nom" => "Génie Informatique",          "code" => "GI",   "nb" => 40, "badge" => "badge-langue"],
    ];

    // Nb soutenances par prof (top 5 mock)
    $soutenancesParProf = [
        "Prof. Ahmed"  => 8,
        "Prof. Sara"   => 7,
        "Prof. Karim"  => 6,
        "Prof. Amal"   => 6,
        "Prof. Youssef"=> 5,
    ];

    // Nb étudiants encadrés par prof (top 5 mock)
    $encadrParProf = [
        "Prof. Ahmed"  => 5,
        "Prof. Sara"   => 6,
        "Prof. Karim"  => 4,
        "Prof. Amal"   => 5,
        "Prof. Youssef"=> 4,
    ];

    $totalFiliere = array_sum(array_column($filieres, 'nb'));
?>

<!-- ── SIDEBAR ── -->
<div class="sidebar">
    <div class="sidebar-logo">
        <i class="bi bi-mortarboard-fill"></i> PFE Manager
    </div>

    <a href="dashboard.view.php" class="nav-item-custom active">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="planning.view.php" class="nav-item-custom">
        <i class="bi bi-calendar3"></i> Planning
    </a>
    <a href="verification.view.php" class="nav-item-custom">
        <i class="bi bi-shield-check"></i> Vérification
    </a>
</div>

<!-- ── MAIN ── -->
<div class="main">

    <!-- Header -->
    <div class="page-header">
        <div>
            <div class="page-title">Dashboard <span style="color:var(--primary)">PFE</span></div>
            <div class="page-sub">Vue d'ensemble de la session de soutenances</div>
        </div>
        <span class="badge-session"><i class="bi bi-calendar-event"></i> Session 2024-2025</span>
    </div>

    <!-- KPI Cards -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-icon blue"><i class="bi bi-people-fill"></i></div>
            <div>
                <div class="kpi-label">Étudiants</div>
                <div class="kpi-value"><?= $nbEtudiants ?></div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon green"><i class="bi bi-person-badge-fill"></i></div>
            <div>
                <div class="kpi-label">Professeurs</div>
                <div class="kpi-value"><?= $nbProfs ?></div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon red"><i class="bi bi-file-earmark-text-fill"></i></div>
            <div>
                <div class="kpi-label">Soutenances</div>
                <div class="kpi-value"><?= $nbSoutenances ?></div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon orange"><i class="bi bi-calendar-check-fill"></i></div>
            <div>
                <div class="kpi-label">Jours</div>
                <div class="kpi-value"><?= $nbJours ?></div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="charts-grid">

        <!-- Graphique barres : Soutenances par prof -->
        <div class="chart-card">
            <div class="chart-title">
                <i class="bi bi-bar-chart-fill"></i>
                Soutenances par professeur (Top 5)
            </div>
            <canvas id="chartSoutenances" height="140"></canvas>
        </div>

        <!-- Graphique doughnut : filières -->
        <div class="chart-card">
            <div class="chart-title">
                <i class="bi bi-pie-chart-fill"></i>
                Répartition par filière
            </div>
            <canvas id="chartFilieres" height="140"></canvas>
        </div>

    </div>

    <!-- Table filières + graphique encadrement -->
    <div class="charts-grid">

        <!-- Table filières -->
        <div class="table-card">
            <div class="chart-title">
                <i class="bi bi-table"></i>
                Détail par filière
            </div>
            <table class="table table-borderless">
                <thead>
                    <tr>
                        <th>Filière</th>
                        <th>Code</th>
                        <th>Étudiants</th>
                        <th>Progression</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($filieres as $f): ?>
                    <tr>
                        <td><?= $f['nom'] ?></td>
                        <td><span class="badge-filiere <?= $f['badge'] ?>"><?= $f['code'] ?></span></td>
                        <td><strong><?= $f['nb'] ?></strong></td>
                        <td style="width:120px">
                            <div class="progress-bar-custom">
                                <div class="progress-fill" style="width:<?= round($f['nb']/$totalFiliere*100) ?>%"></div>
                            </div>
                            <small style="color:var(--muted);font-size:.75rem"><?= round($f['nb']/$totalFiliere*100) ?>%</small>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Graphique encadrement par prof -->
        <div class="chart-card">
            <div class="chart-title">
                <i class="bi bi-people-fill"></i>
                Étudiants encadrés (Top 5)
            </div>
            <canvas id="chartEncadr" height="140"></canvas>
        </div>

    </div>

</div><!-- /main -->

<!-- ── CHART.JS SCRIPTS ── -->
<script>
    /* Palette */
    const colors = ['#1a56db','#0e9f6e','#ff8c00','#e02424','#7e3af2'];
    const colorsLight = ['#dbeafe','#d1fae5','#fff3cd','#fee2e2','#ede9fe'];

    /* 1. Soutenances par prof */
    new Chart(document.getElementById('chartSoutenances'), {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_keys($soutenancesParProf)) ?>,
            datasets: [{
                label: 'Nb soutenances',
                data: <?= json_encode(array_values($soutenancesParProf)) ?>,
                backgroundColor: colors,
                borderRadius: 8,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: '#f0f0f0' }, ticks: { stepSize: 2 } },
                x: { grid: { display: false } }
            }
        }
    });

    /* 2. Filières Doughnut */
    new Chart(document.getElementById('chartFilieres'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_column($filieres, 'code')) ?>,
            datasets: [{
                data: <?= json_encode(array_column($filieres, 'nb')) ?>,
                backgroundColor: colors,
                hoverOffset: 6,
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true,
            cutout: '65%',
            plugins: { legend: { position: 'bottom' } }
        }
    });

    /* 3. Encadrement par prof */
    new Chart(document.getElementById('chartEncadr'), {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_keys($encadrParProf)) ?>,
            datasets: [{
                label: 'Étudiants encadrés',
                data: <?= json_encode(array_values($encadrParProf)) ?>,
                backgroundColor: '#0e9f6e',
                borderRadius: 8,
                borderSkipped: false,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { beginAtZero: true, grid: { color: '#f0f0f0' }, ticks: { stepSize: 1 } },
                y: { grid: { display: false } }
            }
        }
    });
</script>

</body>
</html>