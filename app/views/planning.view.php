<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planning - Soutenances PFE</title>

    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;600;700&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

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

        /* ── FILTRES ── */
        .filter-bar {
            background: var(--card-bg);
            border-radius: var(--radius);
            padding: 1rem 1.5rem;
            border: 1px solid var(--border);
            box-shadow: 0 2px 8px rgba(0,0,0,.04);
            display: flex;
            gap: 1rem;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .filter-bar label {
            font-size: .8rem;
            font-weight: 600;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .05em;
            margin-bottom: 0;
        }

        .filter-bar select,
        .filter-bar input {
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: .4rem .8rem;
            font-size: .88rem;
            font-family: 'Sora', sans-serif;
            color: var(--text);
            outline: none;
            transition: border-color .2s;
        }

        .filter-bar select:focus,
        .filter-bar input:focus { border-color: var(--primary); }

        /* ── TABLE CARD ── */
        .table-card {
            background: var(--card-bg);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            box-shadow: 0 2px 8px rgba(0,0,0,.05);
            overflow: hidden;
        }

        .table-card-header {
            padding: 1.2rem 1.5rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .table-card-title {
            font-size: 1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .table-card-title i { color: var(--primary); }

        .table { margin-bottom: 0; }

        .table th {
            font-size: .78rem;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--muted);
            font-weight: 600;
            background: #f9fafb;
            border-bottom: 2px solid var(--border);
            padding: .9rem 1rem;
        }

        .table td {
            vertical-align: middle;
            font-size: .88rem;
            padding: .85rem 1rem;
            border-bottom: 1px solid var(--border);
        }

        .table tbody tr:last-child td { border-bottom: none; }
        .table tbody tr:hover { background: #f8faff; }

        /* Badges */
        .badge-salle {
            background: #e8f0ff;
            color: var(--primary);
            padding: .3rem .7rem;
            border-radius: 20px;
            font-size: .78rem;
            font-weight: 600;
            font-family: 'JetBrains Mono', monospace;
        }

        .badge-heure {
            background: #f0fdf4;
            color: #065f46;
            padding: .3rem .7rem;
            border-radius: 20px;
            font-size: .78rem;
            font-weight: 600;
            font-family: 'JetBrains Mono', monospace;
        }

        .badge-session {
            background: var(--primary);
            color: #fff;
            padding: .45rem 1rem;
            border-radius: 20px;
            font-size: .8rem;
            font-family: 'JetBrains Mono', monospace;
        }

        /* Info étudiant */
        .etud-name { font-weight: 600; }
        .etud-filiere {
            font-size: .75rem;
            color: var(--muted);
            margin-top: .1rem;
        }

        /* Jury list */
        .jury-list { display: flex; flex-direction: column; gap: .25rem; }
        .jury-item {
            display: flex; align-items: center; gap: .4rem;
            font-size: .83rem;
        }
        .jury-role {
            font-size: .7rem;
            padding: .15rem .5rem;
            border-radius: 10px;
            font-weight: 600;
        }
        .role-enc  { background: #dbeafe; color: #1d4ed8; }
        .role-prés { background: #d1fae5; color: #065f46; }
        .role-rapp { background: #fef3c7; color: #92400e; }

        /* Nb total tag */
        .total-tag {
            background: #f3f4f6;
            color: var(--muted);
            font-size: .8rem;
            padding: .3rem .8rem;
            border-radius: 20px;
            font-family: 'JetBrains Mono', monospace;
        }
    </style>
</head>

<body>

<?php
    /* ══════════════════════════════════════════
       DONNÉES MOCK — À remplacer par DB plus tard
       ══════════════════════════════════════════ */
    $planning = [
        [
            "date"     => "2025-06-01",
            "heure"    => "09:00 - 10:00",
            "salle"    => "A1",
            "etudiant" => "Ali Benali",
            "filiere"  => "GI",
            "jury"     => [
                ["nom" => "Prof. Ahmed",  "role" => "Encadrant", "classe" => "role-enc"],
                ["nom" => "Prof. Sara",   "role" => "Président",  "classe" => "role-prés"],
                ["nom" => "Prof. Karim",  "role" => "Rapporteur", "classe" => "role-rapp"],
            ]
        ],
        [
            "date"     => "2025-06-01",
            "heure"    => "10:00 - 11:00",
            "salle"    => "B2",
            "etudiant" => "Sara Idrissi",
            "filiere"  => "ID",
            "jury"     => [
                ["nom" => "Prof. Karim",  "role" => "Encadrant",  "classe" => "role-enc"],
                ["nom" => "Prof. Amal",   "role" => "Président",  "classe" => "role-prés"],
                ["nom" => "Prof. Ahmed",  "role" => "Rapporteur", "classe" => "role-rapp"],
            ]
        ],
        [
            "date"     => "2025-06-01",
            "heure"    => "11:00 - 12:00",
            "salle"    => "A1",
            "etudiant" => "Youssef Mrani",
            "filiere"  => "TDAI",
            "jury"     => [
                ["nom" => "Prof. Sara",   "role" => "Encadrant",  "classe" => "role-enc"],
                ["nom" => "Prof. Ali",    "role" => "Président",  "classe" => "role-prés"],
                ["nom" => "Prof. Amal",   "role" => "Rapporteur", "classe" => "role-rapp"],
            ]
        ],
        [
            "date"     => "2025-06-02",
            "heure"    => "09:00 - 10:00",
            "salle"    => "C3",
            "etudiant" => "Nadia Chaoui",
            "filiere"  => "ID",
            "jury"     => [
                ["nom" => "Prof. Ali",    "role" => "Encadrant",  "classe" => "role-enc"],
                ["nom" => "Prof. Ahmed",  "role" => "Président",  "classe" => "role-prés"],
                ["nom" => "Prof. Sara",   "role" => "Rapporteur", "classe" => "role-rapp"],
            ]
        ],
        [
            "date"     => "2025-06-02",
            "heure"    => "14:00 - 15:00",
            "salle"    => "B2",
            "etudiant" => "Omar Tazi",
            "filiere"  => "GI",
            "jury"     => [
                ["nom" => "Prof. Amal",   "role" => "Encadrant",  "classe" => "role-enc"],
                ["nom" => "Prof. Karim",  "role" => "Président",  "classe" => "role-prés"],
                ["nom" => "Prof. Ali",    "role" => "Rapporteur", "classe" => "role-rapp"],
            ]
        ],
    ];
?>

<!-- ── SIDEBAR ── -->
<div class="sidebar">
    <div class="sidebar-logo">
        <i class="bi bi-mortarboard-fill"></i> PFE Manager
    </div>
    <a href="dashboard.view.php" class="nav-item-custom">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="planning.view.php" class="nav-item-custom active">
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
            <div class="page-title">Planning des <span style="color:var(--primary)">soutenances</span></div>
            <div class="page-sub">Calendrier complet — Session 2024-2025</div>
        </div>
        <span class="badge-session">
            <i class="bi bi-calendar-event"></i>
            <?= count($planning) ?> soutenances planifiées
        </span>
    </div>

    <!-- Filtres -->
    <div class="filter-bar">
        <label>Filtrer :</label>

        <div>
            <label>Date</label><br>
            <select id="filtreDate">
                <option value="">Toutes les dates</option>
                <?php
                    $dates = array_unique(array_column($planning, 'date'));
                    foreach ($dates as $d) echo "<option value='$d'>$d</option>";
                ?>
            </select>
        </div>

        <div>
            <label>Salle</label><br>
            <select id="filtreSalle">
                <option value="">Toutes les salles</option>
                <?php
                    $salles = array_unique(array_column($planning, 'salle'));
                    foreach ($salles as $s) echo "<option value='$s'>$s</option>";
                ?>
            </select>
        </div>

        <div>
            <label>Recherche étudiant</label><br>
            <input type="text" id="rechercheEtud" placeholder="Nom étudiant...">
        </div>

        <button onclick="resetFiltres()"
                style="margin-top:auto;background:var(--primary);color:#fff;border:none;padding:.4rem 1rem;border-radius:8px;font-size:.85rem;cursor:pointer;">
            <i class="bi bi-arrow-counterclockwise"></i> Reset
        </button>
    </div>

    <!-- Table -->
    <div class="table-card">
        <div class="table-card-header">
            <div class="table-card-title">
                <i class="bi bi-calendar3"></i> Tableau du planning
            </div>
            <span class="total-tag" id="nbLignes"><?= count($planning) ?> entrées</span>
        </div>

        <div class="table-responsive">
            <table class="table" id="tablePlanning">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Horaire</th>
                        <th>Salle</th>
                        <th>Étudiant</th>
                        <th>Jury</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($planning as $i => $p): ?>
                    <tr
                        data-date="<?= $p['date'] ?>"
                        data-salle="<?= $p['salle'] ?>"
                        data-etud="<?= strtolower($p['etudiant']) ?>">
                        <td style="color:var(--muted);font-family:'JetBrains Mono',monospace;font-size:.8rem">
                            <?= str_pad($i+1, 2, '0', STR_PAD_LEFT) ?>
                        </td>
                        <td style="font-family:'JetBrains Mono',monospace;font-size:.82rem"><?= $p['date'] ?></td>
                        <td><span class="badge-heure"><?= $p['heure'] ?></span></td>
                        <td><span class="badge-salle"><?= $p['salle'] ?></span></td>
                        <td>
                            <div class="etud-name"><?= $p['etudiant'] ?></div>
                            <div class="etud-filiere"><?= $p['filiere'] ?></div>
                        </td>
                        <td>
                            <div class="jury-list">
                            <?php foreach ($p['jury'] as $j): ?>
                                <div class="jury-item">
                                    <span class="jury-role <?= $j['classe'] ?>"><?= $j['role'] ?></span>
                                    <?= $j['nom'] ?>
                                </div>
                            <?php endforeach; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div><!-- /main -->

<script>
    function filtrer() {
        const date  = document.getElementById('filtreDate').value.toLowerCase();
        const salle = document.getElementById('filtreSalle').value.toLowerCase();
        const etud  = document.getElementById('rechercheEtud').value.toLowerCase();

        const rows = document.querySelectorAll('#tablePlanning tbody tr');
        let count = 0;

        rows.forEach(row => {
            const ok =
                (!date  || row.dataset.date  === date) &&
                (!salle || row.dataset.salle.toLowerCase() === salle) &&
                (!etud  || row.dataset.etud.includes(etud));
            row.style.display = ok ? '' : 'none';
            if (ok) count++;
        });

        document.getElementById('nbLignes').textContent = count + ' entrées';
    }

    function resetFiltres() {
        document.getElementById('filtreDate').value = '';
        document.getElementById('filtreSalle').value = '';
        document.getElementById('rechercheEtud').value = '';
        filtrer();
    }

    document.getElementById('filtreDate').addEventListener('change', filtrer);
    document.getElementById('filtreSalle').addEventListener('change', filtrer);
    document.getElementById('rechercheEtud').addEventListener('input', filtrer);
</script>

</body>
</html>