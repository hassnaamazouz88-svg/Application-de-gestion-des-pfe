<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Gestion PFE</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        /* ══════════════════════════════════════════
           VARIABLES & BASE
        ══════════════════════════════════════════ */
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
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            font-size: 14px;
        }

        /* ══════════════════════════════════════════
           SIDEBAR
        ══════════════════════════════════════════ */
        .sidebar {
            position: fixed; top: 0; left: 0;
            width: var(--sidebar-w); height: 100vh;
            background: #0f172a;
            display: flex; flex-direction: column;
            padding: 0; z-index: 100;
            overflow-y: auto;
        }
        .sidebar-brand {
            padding: 20px 20px 16px;
            border-bottom: 1px solid rgba(255,255,255,.08);
        }
        .sidebar-brand .brand-name {
            font-size: 15px; font-weight: 700;
            color: #fff; letter-spacing: -.02em;
        }
        .sidebar-brand .brand-sub {
            font-size: 10px; color: #94a3b8;
            margin-top: 2px;
        }
        .sidebar-section {
            padding: 20px 12px 8px;
            font-size: 10px; font-weight: 600;
            color: #475569; text-transform: uppercase;
            letter-spacing: .08em;
        }
        .nav-link-item {
            display: flex; align-items: center; gap: 10px;
            padding: 9px 12px; margin: 1px 8px;
            border-radius: 8px;
            color: #94a3b8; text-decoration: none;
            font-size: 13px; font-weight: 500;
            transition: all .15s;
        }
        .nav-link-item:hover { background: rgba(255,255,255,.07); color: #e2e8f0; }
        .nav-link-item.active { background: var(--blue); color: #fff; }
        .nav-link-item i { font-size: 15px; width: 18px; text-align: center; }
        .sidebar-footer {
            margin-top: auto; padding: 16px 20px;
            border-top: 1px solid rgba(255,255,255,.07);
            font-size: 10px; color: #475569;
        }

        /* ══════════════════════════════════════════
           MAIN CONTENT
        ══════════════════════════════════════════ */
        .main {
            margin-left: var(--sidebar-w);
            min-height: 100vh;
            padding: 28px 32px;
        }

        /* ══════════════════════════════════════════
           PAGE HEADER
        ══════════════════════════════════════════ */
        .page-header {
            margin-bottom: 28px;
            display: flex; align-items: flex-start;
            justify-content: space-between; flex-wrap: wrap; gap: 12px;
        }
        .page-title {
            font-size: 22px; font-weight: 700;
            color: var(--text); letter-spacing: -.03em;
        }
        .page-title span { color: var(--blue); }
        .page-meta {
            font-size: 12px; color: var(--slate);
            margin-top: 4px;
        }
        .session-badge {
            background: var(--blue); color: #fff;
            padding: 6px 14px; border-radius: 20px;
            font-size: 12px; font-weight: 600;
            white-space: nowrap;
        }

        /* ══════════════════════════════════════════
           ALERT BANNER
        ══════════════════════════════════════════ */
        .alert-banner {
            border-radius: 10px; padding: 12px 16px;
            margin-bottom: 24px;
            display: flex; align-items: center; gap: 10px;
            font-size: 13px; font-weight: 500;
            border: 1px solid;
        }
        .alert-banner.ok {
            background: var(--green-lt);
            border-color: #bbf7d0; color: var(--green);
        }
        .alert-banner.warn {
            background: var(--amber-lt);
            border-color: #fde68a; color: var(--amber);
        }
        .alert-banner.err {
            background: var(--red-lt);
            border-color: #fecaca; color: var(--red);
        }

        /* ══════════════════════════════════════════
           KPI CARDS
        ══════════════════════════════════════════ */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
            margin-bottom: 24px;
        }
        @media (max-width: 1100px) { .kpi-grid { grid-template-columns: repeat(2,1fr); } }
        .kpi-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 18px 20px;
            display: flex; flex-direction: column; gap: 10px;
            transition: box-shadow .2s;
        }
        .kpi-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.06); }
        .kpi-top {
            display: flex; align-items: center;
            justify-content: space-between;
        }
        .kpi-icon {
            width: 38px; height: 38px;
            border-radius: 9px;
            display: flex; align-items: center;
            justify-content: center; font-size: 16px;
        }
        .kpi-icon.blue   { background: var(--blue-lt); color: var(--blue); }
        .kpi-icon.green  { background: var(--green-lt); color: var(--green); }
        .kpi-icon.amber  { background: var(--amber-lt); color: var(--amber); }
        .kpi-icon.red    { background: var(--red-lt); color: var(--red); }
        .kpi-badge {
            font-size: 10px; font-weight: 600;
            padding: 3px 8px; border-radius: 20px;
        }
        .kpi-badge.up { background: var(--green-lt); color: var(--green); }
        .kpi-badge.ok { background: var(--blue-lt); color: var(--blue); }
        .kpi-badge.warn { background: var(--amber-lt); color: var(--amber); }
        .kpi-value {
            font-size: 28px; font-weight: 700;
            letter-spacing: -.04em; color: var(--text);
            line-height: 1;
        }
        .kpi-label {
            font-size: 12px; color: var(--slate); margin-top: 2px;
        }
        .kpi-sub { font-size: 11px; color: #94a3b8; }

        /* ══════════════════════════════════════════
           PROGRESS CARDS
        ══════════════════════════════════════════ */
        .progress-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px; margin-bottom: 24px;
        }
        .prog-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 12px; padding: 18px 20px;
        }
        .prog-header {
            display: flex; align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
        }
        .prog-title {
            font-size: 13px; font-weight: 600;
            display: flex; align-items: center; gap: 7px;
        }
        .prog-title i { color: var(--blue); }
        .prog-pct {
            font-size: 15px; font-weight: 700;
            color: var(--blue);
        }
        .prog-bar {
            height: 7px; background: #f1f5f9;
            border-radius: 4px; overflow: hidden;
        }
        .prog-fill {
            height: 100%; border-radius: 4px;
            background: var(--blue); transition: width .6s ease;
        }
        .prog-fill.green { background: var(--green); }
        .prog-sub { font-size: 11px; color: var(--slate); margin-top: 7px; }

        /* ══════════════════════════════════════════
           ACTION CARDS (grid 4 cols)
        ══════════════════════════════════════════ */
        .section-label {
            font-size: 11px; font-weight: 600;
            color: var(--slate); text-transform: uppercase;
            letter-spacing: .08em; margin-bottom: 12px;
        }
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px; margin-bottom: 24px;
        }
        @media (max-width: 900px) { .actions-grid { grid-template-columns: repeat(2,1fr); } }
        .action-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 12px; padding: 18px 16px;
            text-decoration: none; color: inherit;
            display: flex; flex-direction: column;
            align-items: flex-start; gap: 10px;
            transition: all .2s; cursor: pointer;
        }
        .action-card:hover {
            border-color: var(--blue);
            box-shadow: 0 0 0 3px rgba(37,99,235,.08);
            color: inherit;
        }
        .action-card .ac-icon {
            width: 40px; height: 40px;
            border-radius: 10px;
            display: flex; align-items: center;
            justify-content: center; font-size: 17px;
        }
        .action-card .ac-title {
            font-size: 13px; font-weight: 600;
        }
        .action-card .ac-sub {
            font-size: 11px; color: var(--slate);
        }
        /* couleurs icônes */
        .ic-blue   { background: var(--blue-lt); color: var(--blue); }
        .ic-green  { background: var(--green-lt); color: var(--green); }
        .ic-amber  { background: var(--amber-lt); color: var(--amber); }
        .ic-red    { background: var(--red-lt); color: var(--red); }
        .ic-purple { background: #f5f3ff; color: #7c3aed; }
        .ic-teal   { background: #f0fdfa; color: #0d9488; }
        .ic-pink   { background: #fdf2f8; color: #db2777; }
        .ic-slate  { background: #f8fafc; color: #475569; }

        /* ══════════════════════════════════════════
           TABLE CARD
        ══════════════════════════════════════════ */
        .table-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 12px; overflow: hidden;
            margin-bottom: 24px;
        }
        .table-card-header {
            padding: 14px 20px;
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center;
            justify-content: space-between;
        }
        .table-card-title {
            font-size: 13px; font-weight: 600;
            display: flex; align-items: center; gap: 7px;
        }
        .table-card-title i { color: var(--blue); }
        .table-count {
            font-size: 11px; font-weight: 600;
            background: var(--blue-lt); color: var(--blue);
            padding: 3px 10px; border-radius: 20px;
        }
        .tbl { width: 100%; border-collapse: collapse; }
        .tbl th {
            font-size: 11px; font-weight: 600;
            color: var(--slate); text-transform: uppercase;
            letter-spacing: .05em; padding: 10px 16px;
            background: #f8fafc; border-bottom: 1px solid var(--border);
            text-align: left;
        }
        .tbl td {
            padding: 10px 16px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 13px; vertical-align: middle;
        }
        .tbl tbody tr:last-child td { border-bottom: none; }
        .tbl tbody tr:hover td { background: #fafbff; }
        .pill {
            display: inline-block;
            padding: 3px 9px; border-radius: 20px;
            font-size: 11px; font-weight: 600;
        }
        .pill-blue  { background: var(--blue-lt); color: var(--blue); }
        .pill-green { background: var(--green-lt); color: var(--green); }
        .pill-amber { background: var(--amber-lt); color: var(--amber); }
    </style>
</head>
<body>

<?php
/* ══════════════════════════════════════════
   DONNÉES — connexion DB + requêtes
══════════════════════════════════════════ */
try {
    $db = new PDO(
        "mysql:host=localhost;dbname=gestion_pfe;charset=utf8",
        "root", "",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (Exception $e) {
    die("Erreur connexion DB : " . $e->getMessage());
}

$nbEtudiants      = (int)$db->query("SELECT COUNT(*) FROM Etudiant")->fetchColumn();
$nbProfs          = (int)$db->query("SELECT COUNT(*) FROM Professeur")->fetchColumn();
$nbSoutenances    = (int)$db->query("SELECT COUNT(*) FROM Soutenance")->fetchColumn();
$nbCreneaux       = (int)$db->query("SELECT COUNT(*) FROM Creneau")->fetchColumn();
$nbCreneauxLibres = (int)$db->query("SELECT COUNT(*) FROM Creneau c LEFT JOIN Soutenance s ON c.id_cren=s.id_cren WHERE s.id_cren IS NULL")->fetchColumn();
$nbJours          = (int)$db->query("SELECT COUNT(DISTINCT date_cren) FROM Creneau")->fetchColumn();
$nbSansEncadrant  = (int)$db->query("SELECT COUNT(*) FROM Etudiant WHERE id_prof IS NULL")->fetchColumn();

// Taux de planning
$tauxPlanning = $nbEtudiants > 0 ? round($nbSoutenances / $nbEtudiants * 100) : 0;
$tauxEncadr   = $nbEtudiants > 0 ? round(($nbEtudiants - $nbSansEncadrant) / $nbEtudiants * 100) : 0;

// Répartition par filière
$filieres = $db->query(
    "SELECT filiere, COUNT(*) as nb FROM Etudiant WHERE filiere IS NOT NULL GROUP BY filiere ORDER BY nb DESC"
)->fetchAll(PDO::FETCH_ASSOC);

// Top 5 profs encadrants
$topProfs = $db->query(
    "SELECT CONCAT(p.nom,' ',p.prenom) AS prof, COUNT(e.id_etud) AS nb
     FROM Professeur p LEFT JOIN Etudiant e ON p.id_prof=e.id_prof
     GROUP BY p.id_prof ORDER BY nb DESC LIMIT 5"
)->fetchAll(PDO::FETCH_ASSOC);

// Alerte système
if ($nbSansEncadrant > 0) {
    $alertClass = 'warn';
    $alertIcon  = 'bi-exclamation-triangle-fill';
    $alertMsg   = "{$nbSansEncadrant} étudiant(s) sans encadrant. Lancez l'affectation automatique.";
} else {
    $alertClass = 'ok';
    $alertIcon  = 'bi-shield-fill-check';
    $alertMsg   = "Tous les étudiants ont un encadrant. Encadrements complets.";
}
?>

<!-- ══════════════════════════════════════════
     SIDEBAR
══════════════════════════════════════════ -->
<div class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-name"><i class="bi bi-mortarboard-fill" style="color:#60a5fa"></i> PFE Manager</div>
        <div class="brand-sub">Session 2024-2025</div>
    </div>

    <div class="sidebar-section">Navigation</div>
    <a href="dashboard.view.php"    class="nav-link-item active"><i class="bi bi-grid-1x2"></i> Dashboard</a>
    <a href="planning.view.php"     class="nav-link-item"><i class="bi bi-calendar3"></i> Planning
        <span style="margin-left:auto;background:#2563eb;color:#fff;font-size:10px;padding:1px 7px;border-radius:10px"><?= $nbSoutenances ?></span>
    </a>
    <a href="verification.view.php" class="nav-link-item"><i class="bi bi-shield-check"></i> Vérification</a>

    <div class="sidebar-section">Actions</div>
    <a href="?action=import"          class="nav-link-item"><i class="bi bi-upload"></i> Importer Excel</a>
    <a href="?action=affectation"     class="nav-link-item"><i class="bi bi-person-lines-fill"></i> Affectation</a>
    <a href="?action=pdf_planning"    class="nav-link-item"><i class="bi bi-file-earmark-pdf"></i> PDF Planning</a>
    <a href="?action=pdf_affectation" class="nav-link-item"><i class="bi bi-file-earmark-pdf"></i> PDF Affectation</a>

    <div class="sidebar-footer">
        Gestion PFE © 2025<br>Session 2024-2025
    </div>
</div>

<!-- ══════════════════════════════════════════
     MAIN
══════════════════════════════════════════ -->
<div class="main">

    <!-- Header -->
    <div class="page-header">
        <div>
            <div class="page-title">Tableau de bord <span>PFE</span></div>
            <div class="page-meta">
                <i class="bi bi-circle-fill" style="color:#16a34a;font-size:8px"></i>
                Session 2024-2025 &nbsp;·&nbsp; Mis à jour le <?= date('d/m/Y à H:i') ?>
            </div>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
            <a href="?action=import"      class="btn btn-sm btn-outline-secondary"><i class="bi bi-upload"></i> Importer</a>
            <a href="?action=affectation" class="btn btn-sm" style="background:#16a34a;color:#fff"><i class="bi bi-person-lines-fill"></i> Affectation</a>
            <a href="verification.view.php" class="btn btn-sm" style="background:#d97706;color:#fff"><i class="bi bi-shield-check"></i> Vérifier</a>
            <a href="?action=pdf_planning"  class="btn btn-sm" style="background:#2563eb;color:#fff"><i class="bi bi-file-pdf"></i> PDF Planning</a>
        </div>
    </div>

    <!-- Alerte -->
    <div class="alert-banner <?= $alertClass ?>">
        <i class="bi <?= $alertIcon ?>"></i>
        <strong><?= htmlspecialchars($alertMsg) ?></strong>
    </div>

    <!-- KPI Grid -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-top">
                <div class="kpi-icon blue"><i class="bi bi-people-fill"></i></div>
                <span class="kpi-badge up">↑ Actifs</span>
            </div>
            <div>
                <div class="kpi-value"><?= $nbEtudiants ?></div>
                <div class="kpi-label">Étudiants</div>
                <div class="kpi-sub"><?= $nbSansEncadrant ?> sans encadrant</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-top">
                <div class="kpi-icon green"><i class="bi bi-person-badge-fill"></i></div>
                <span class="kpi-badge ok">✓ OK</span>
            </div>
            <div>
                <div class="kpi-value"><?= $nbProfs ?></div>
                <div class="kpi-label">Professeurs</div>
                <div class="kpi-sub">Corps enseignant</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-top">
                <div class="kpi-icon amber"><i class="bi bi-file-earmark-text-fill"></i></div>
                <span class="kpi-badge ok">↑ Planifiées</span>
            </div>
            <div>
                <div class="kpi-value"><?= $nbSoutenances ?></div>
                <div class="kpi-label">Soutenances</div>
                <div class="kpi-sub">/ <?= $nbEtudiants ?> étudiants</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-top">
                <div class="kpi-icon red"><i class="bi bi-calendar3"></i></div>
                <span class="kpi-badge warn"><?= $nbJours ?> jours</span>
            </div>
            <div>
                <div class="kpi-value"><?= $nbCreneaux ?></div>
                <div class="kpi-label">Créneaux total</div>
                <div class="kpi-sub"><?= $nbCreneauxLibres ?> disponibles</div>
            </div>
        </div>
    </div>

    <!-- Progress bars -->
    <div class="progress-row">
        <div class="prog-card">
            <div class="prog-header">
                <div class="prog-title"><i class="bi bi-calendar-check"></i> Avancement du planning</div>
                <div class="prog-pct"><?= $tauxPlanning ?>%</div>
            </div>
            <div class="prog-bar">
                <div class="prog-fill" style="width:<?= $tauxPlanning ?>%"></div>
            </div>
            <div class="prog-sub"><?= $nbSoutenances ?> soutenances planifiées sur <?= $nbEtudiants ?> étudiants</div>
        </div>
        <div class="prog-card">
            <div class="prog-header">
                <div class="prog-title"><i class="bi bi-person-check"></i> Couverture encadrement</div>
                <div class="prog-pct" style="color:var(--green)"><?= $tauxEncadr ?>%</div>
            </div>
            <div class="prog-bar">
                <div class="prog-fill green" style="width:<?= $tauxEncadr ?>%"></div>
            </div>
            <div class="prog-sub"><?= $nbEtudiants - $nbSansEncadrant ?> étudiants encadrés sur <?= $nbEtudiants ?></div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="section-label">Actions rapides</div>
    <div class="actions-grid">
        <a href="?action=import" class="action-card">
            <div class="ac-icon ic-blue"><i class="bi bi-file-earmark-excel"></i></div>
            <div>
                <div class="ac-title">Importer Excel</div>
                <div class="ac-sub">Étudiants &amp; Professeurs</div>
            </div>
        </a>
        <a href="?action=affectation" class="action-card">
            <div class="ac-icon ic-green"><i class="bi bi-person-lines-fill"></i></div>
            <div>
                <div class="ac-title">Générer Affectation</div>
                <div class="ac-sub">Encadrants automatiques</div>
            </div>
        </a>
        <a href="?action=planning" class="action-card">
            <div class="ac-icon ic-purple"><i class="bi bi-calendar2-range"></i></div>
            <div>
                <div class="ac-title">Générer Planning</div>
                <div class="ac-sub">Soutenances &amp; créneaux</div>
            </div>
        </a>
        <a href="verification.view.php" class="action-card">
            <div class="ac-icon ic-amber"><i class="bi bi-shield-exclamation"></i></div>
            <div>
                <div class="ac-title">Vérifier</div>
                <div class="ac-sub">Contraintes &amp; conflits</div>
            </div>
        </a>
        <a href="?action=pdf_planning" class="action-card">
            <div class="ac-icon ic-red"><i class="bi bi-file-earmark-pdf"></i></div>
            <div>
                <div class="ac-title">PDF Planning</div>
                <div class="ac-sub">Export complet</div>
            </div>
        </a>
        <a href="?action=pdf_affectation" class="action-card">
            <div class="ac-icon ic-pink"><i class="bi bi-file-earmark-pdf"></i></div>
            <div>
                <div class="ac-title">PDF Affectation</div>
                <div class="ac-sub">Liste encadrants</div>
            </div>
        </a>
        <a href="planning.view.php" class="action-card">
            <div class="ac-icon ic-teal"><i class="bi bi-table"></i></div>
            <div>
                <div class="ac-title">Voir Planning</div>
                <div class="ac-sub">Tableau interactif</div>
            </div>
        </a>
        <a href="?action=refresh" class="action-card">
            <div class="ac-icon ic-slate"><i class="bi bi-arrow-repeat"></i></div>
            <div>
                <div class="ac-title">Actualiser</div>
                <div class="ac-sub">Rafraîchir les données</div>
            </div>
        </a>
    </div>

    <!-- Tables côte à côte -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">

        <!-- Répartition filières -->
        <div class="table-card">
            <div class="table-card-header">
                <div class="table-card-title"><i class="bi bi-bar-chart"></i> Répartition par filière</div>
                <span class="table-count"><?= count($filieres) ?> filières</span>
            </div>
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Filière</th>
                        <th>Étudiants</th>
                        <th>Part</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $total = array_sum(array_column($filieres, 'nb'));
                $colors = ['pill-blue','pill-green','pill-amber'];
                foreach ($filieres as $i => $f):
                    $pct = $total > 0 ? round($f['nb']/$total*100) : 0;
                ?>
                <tr>
                    <td>
                        <span class="pill <?= $colors[$i % count($colors)] ?>">
                            <?= htmlspecialchars($f['filiere']) ?>
                        </span>
                    </td>
                    <td><strong><?= $f['nb'] ?></strong></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:8px">
                            <div style="flex:1;height:5px;background:#f1f5f9;border-radius:3px;overflow:hidden">
                                <div style="width:<?= $pct ?>%;height:100%;background:#2563eb;border-radius:3px"></div>
                            </div>
                            <span style="font-size:11px;color:var(--slate);width:28px"><?= $pct ?>%</span>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Top profs encadrants -->
        <div class="table-card">
            <div class="table-card-header">
                <div class="table-card-title"><i class="bi bi-trophy"></i> Top encadrants</div>
                <span class="table-count">Top 5</span>
            </div>
            <table class="tbl">
                <thead>
                    <tr><th>#</th><th>Professeur</th><th>Étudiants encadrés</th></tr>
                </thead>
                <tbody>
                <?php foreach ($topProfs as $i => $p): ?>
                <tr>
                    <td style="color:var(--slate);font-size:12px"><?= str_pad($i+1,2,'0',STR_PAD_LEFT) ?></td>
                    <td><?= htmlspecialchars($p['prof']) ?></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:8px">
                            <div style="flex:1;height:5px;background:#f1f5f9;border-radius:3px;overflow:hidden">
                                <div style="width:<?= $topProfs[0]['nb'] > 0 ? round($p['nb']/$topProfs[0]['nb']*100) : 0 ?>%;height:100%;background:#16a34a;border-radius:3px"></div>
                            </div>
                            <strong style="font-size:13px;width:20px"><?= $p['nb'] ?></strong>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>

</div><!-- /main -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>