<?php
try {
    $db = new PDO("mysql:host=localhost;dbname=gestion_pfe;charset=utf8","root","",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (Exception $e) { die("Erreur connexion DB : " . $e->getMessage()); }

$sql = "
    SELECT 
        s.id_stnc, s.num_salle,
        c.date        AS date,
        c.heure_debut AS heure,
        c.heure_fin,
        e.nom AS etud_nom, e.prenom AS etud_prenom,
        e.filiere, e.langue_pfe,
        GROUP_CONCAT(p.id_prof) AS profs_ids,
        GROUP_CONCAT(p.specialite) AS profs_specs,
        GROUP_CONCAT(CONCAT(p.nom,' ',p.prenom) ORDER BY pa.role_jury SEPARATOR '|') AS profs_noms
    FROM Soutenance s
    JOIN Creneau    c  ON s.id_cren  = c.id_cren
    JOIN Etudiant   e  ON s.id_etud  = e.id_etud
    JOIN Participer pa ON s.id_stnc  = pa.id_stnc
    JOIN Professeur p  ON pa.id_prof = p.id_prof
    GROUP BY s.id_stnc
    ORDER BY c.date, c.heure_debut
";
$soutenances = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$rows = $db->query("SELECT CONCAT(p.nom,' ',p.prenom) AS prof, COUNT(e.id_etud) AS nb FROM Professeur p LEFT JOIN Etudiant e ON p.id_prof=e.id_prof GROUP BY p.id_prof ORDER BY nb DESC")->fetchAll(PDO::FETCH_ASSOC);
$encadrStats = [];
foreach ($rows as $r) $encadrStats[$r['prof']] = (int)$r['nb'];

// ── Vérifications ──
$erreursCritiques = [];
$avertissements   = [];
$infos            = [];

// 1. Chevauchement salle
$seen = [];
foreach ($soutenances as $s) {
    $key = $s['date'].'|'.$s['heure'].'|'.$s['num_salle'];
    if (isset($seen[$key])) {
        $erreursCritiques[] = "Chevauchement salle {$s['num_salle']} : deux soutenances le {$s['date']} à {$s['heure']}";
    }
    $seen[$key] = true;
}

// 2. Double affectation prof
$profSlots = [];
foreach ($soutenances as $s) {
    $ids  = array_map('trim', explode(',', $s['profs_ids'] ?? ''));
    $noms = explode('|', $s['profs_noms'] ?? '');
    foreach ($ids as $i => $profId) {
        if (empty($profId)) continue;
        $key = $profId.'|'.$s['date'].'|'.$s['heure'];
        $nom = $noms[$i] ?? "Prof #$profId";
        if (isset($profSlots[$key])) {
            $erreursCritiques[] = "Double affectation : $nom dans 2 salles le {$s['date']} à {$s['heure']}";
        }
        $profSlots[$key] = $nom;
    }
}
$erreursCritiques = array_unique($erreursCritiques);

// 3. Repos insuffisant
$profHeures = [];
foreach ($soutenances as $s) {
    $ids  = array_map('trim', explode(',', $s['profs_ids'] ?? ''));
    $noms = explode('|', $s['profs_noms'] ?? '');
    foreach ($ids as $i => $profId) {
        if (empty($profId)) continue;
        $nom = $noms[$i] ?? "Prof #$profId";
        $profHeures[$profId]['nom']    = $nom;
        $profHeures[$profId]['slots'][] = ['date'=>$s['date'],'heure'=>$s['heure']];
    }
}
foreach ($profHeures as $data) {
    $slots = $data['slots']; $nom = $data['nom'];
    usort($slots, fn($a,$b) => strcmp($a['date'].$a['heure'], $b['date'].$b['heure']));
    for ($i = 0; $i < count($slots)-1; $i++) {
        if ($slots[$i]['date'] !== $slots[$i+1]['date']) continue;
        $diff = (strtotime($slots[$i+1]['heure']) - strtotime($slots[$i]['heure'])) / 3600;
        if ($diff < 1) {
            $avertissements[] = "Repos insuffisant : $nom — {$diff}h entre {$slots[$i]['heure']} et {$slots[$i+1]['heure']} le {$slots[$i]['date']}";
        }
    }
}

// 4. Équilibre encadrement
if (!empty($encadrStats)) {
    $avg = array_sum($encadrStats) / count($encadrStats);
    foreach ($encadrStats as $prof => $nb) {
        if ($nb === 0) $avertissements[] = "$prof n'encadre aucun étudiant";
        elseif ($nb > $avg + 1.5) $avertissements[] = "Déséquilibre : $prof encadre $nb étudiants (moyenne = ".round($avg,1).")";
    }
}

// 5. Contraintes OK
$juryOk = true; $langueOk = true;
foreach ($soutenances as $s) {
    $specs  = array_map('trim', explode(',', $s['profs_specs'] ?? ''));
    $nbInfo = count(array_filter($specs, fn($sp) => stripos($sp,'informatique') !== false));
    if ($nbInfo < 2) $juryOk = false;
    if (strtolower($s['langue_pfe'] ?? '') === 'anglais') {
        $hasLang = count(array_filter($specs, fn($sp) => stripos($sp,'anglais') !== false || stripos($sp,'langue') !== false)) > 0;
        if (!$hasLang) $langueOk = false;
    }
}
if ($juryOk)   $infos[] = "Tous les jurys ont au moins 2 profs informatique";
if ($langueOk) $infos[] = "Toutes les PFE en anglais ont un prof de langue dans le jury";
if (empty($erreursCritiques)) $infos[] = "Aucun chevauchement de salle détecté";

// Types anomalies pour graphique
$typesAnomalies = [];
foreach ($erreursCritiques as $e) {
    if (stripos($e,'chevauchement') !== false) @$typesAnomalies['Chevauchements']++;
    if (stripos($e,'double')        !== false) @$typesAnomalies['Double affectation']++;
}
foreach ($avertissements as $e) {
    if (stripos($e,'repos')     !== false) @$typesAnomalies['Repos insuffisant']++;
    if (stripos($e,'équilibre') !== false) @$typesAnomalies['Déséquilibre']++;
}

$totalErreurs = count($erreursCritiques) + count($avertissements);
$nbOk         = count($infos);
?>
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
        :root{--primary:#1a56db;--secondary:#0e9f6e;--danger:#e02424;--warning:#ff8c00;--bg:#f0f4ff;--card-bg:#ffffff;--text:#111928;--muted:#6b7280;--border:#e5e7eb;--radius:14px;}
        *{box-sizing:border-box;margin:0;padding:0;}
        body{font-family:'Sora',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;}
        .sidebar{position:fixed;top:0;left:0;width:240px;height:100vh;background:var(--primary);display:flex;flex-direction:column;padding:2rem 1.2rem;z-index:100;}
        .sidebar-logo{font-size:1.3rem;font-weight:700;color:#fff;margin-bottom:2.5rem;display:flex;align-items:center;gap:.6rem;}
        .nav-item-custom{display:flex;align-items:center;gap:.8rem;color:rgba(255,255,255,.75);text-decoration:none;padding:.7rem 1rem;border-radius:10px;font-size:.9rem;font-weight:500;margin-bottom:.3rem;transition:background .2s,color .2s;}
        .nav-item-custom:hover,.nav-item-custom.active{background:rgba(255,255,255,.18);color:#fff;}
        .nav-item-custom i{font-size:1.1rem;}
        .main{margin-left:240px;padding:2rem 2.5rem;}
        .page-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:2rem;}
        .page-title{font-size:1.6rem;font-weight:700;}
        .page-sub{font-size:.85rem;color:var(--muted);margin-top:.2rem;}
        .kpi-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:1.2rem;margin-bottom:2rem;}
        .kpi-card{background:var(--card-bg);border-radius:var(--radius);padding:1.2rem 1.4rem;border:1px solid var(--border);box-shadow:0 2px 8px rgba(0,0,0,.05);display:flex;align-items:center;gap:1rem;}
        .kpi-icon{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0;}
        .kpi-icon.red{background:#fee2e2;color:var(--danger);}
        .kpi-icon.green{background:#d1fae5;color:var(--secondary);}
        .kpi-icon.orange{background:#fff3cd;color:var(--warning);}
        .kpi-icon.blue{background:#e8f0ff;color:var(--primary);}
        .kpi-label{font-size:.78rem;color:var(--muted);margin-bottom:.2rem;}
        .kpi-value{font-size:1.7rem;font-weight:700;font-family:'JetBrains Mono',monospace;}
        .charts-grid{display:grid;grid-template-columns:1fr 1fr;gap:1.2rem;margin-bottom:1.5rem;}
        .chart-card{background:var(--card-bg);border-radius:var(--radius);padding:1.5rem;border:1px solid var(--border);box-shadow:0 2px 8px rgba(0,0,0,.05);}
        .chart-title{font-size:1rem;font-weight:600;margin-bottom:1.2rem;display:flex;align-items:center;gap:.5rem;}
        .chart-title i{color:var(--primary);}
        .anomalie-section{background:var(--card-bg);border-radius:var(--radius);border:1px solid var(--border);box-shadow:0 2px 8px rgba(0,0,0,.05);margin-bottom:1.2rem;overflow:hidden;}
        .anomalie-header{padding:1rem 1.5rem;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--border);}
        .anomalie-header-title{display:flex;align-items:center;gap:.6rem;font-weight:600;font-size:.95rem;}
        .section-rouge .anomalie-header{background:#fff5f5;}
        .section-orange .anomalie-header{background:#fffbeb;}
        .section-bleu .anomalie-header{background:#eff6ff;}
        .badge-count{font-family:'JetBrains Mono',monospace;font-size:.78rem;padding:.25rem .6rem;border-radius:10px;font-weight:700;}
        .badge-count.rouge{background:#fee2e2;color:var(--danger);}
        .badge-count.orange{background:#fff3cd;color:var(--warning);}
        .badge-count.vert{background:#d1fae5;color:var(--secondary);}
        .anomalie-list{padding:0;list-style:none;}
        .anomalie-item{display:flex;align-items:flex-start;gap:.8rem;padding:.9rem 1.5rem;border-bottom:1px solid var(--border);font-size:.88rem;}
        .anomalie-item:last-child{border-bottom:none;}
        .anomalie-item i{font-size:1rem;margin-top:.1rem;flex-shrink:0;}
        .rouge .anomalie-item i{color:var(--danger);}
        .orange .anomalie-item i{color:var(--warning);}
        .alert-ok{background:#d1fae5;border:1px solid #6ee7b7;color:#065f46;border-radius:var(--radius);padding:1.2rem 1.5rem;display:flex;align-items:center;gap:.8rem;font-weight:500;margin-bottom:1.5rem;}
        .btn-verifie{background:var(--primary);color:#fff;border:none;padding:.55rem 1.3rem;border-radius:10px;font-size:.88rem;cursor:pointer;display:flex;align-items:center;gap:.5rem;}
        .btn-verifie:hover{opacity:.88;}
    </style>
</head>
<body>
<div class="sidebar">
    <div class="sidebar-logo"><i class="bi bi-mortarboard-fill"></i> PFE Manager</div>
    <a href="dashboard.view.php"    class="nav-item-custom"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="planning.view.php"     class="nav-item-custom"><i class="bi bi-calendar3"></i> Planning</a>
    <a href="verification.view.php" class="nav-item-custom active"><i class="bi bi-shield-check"></i> Vérification</a>
</div>
<div class="main">
    <div class="page-header">
        <div>
            <div class="page-title">Vérification des <span style="color:var(--primary)">contraintes</span></div>
            <div class="page-sub">Contrôle automatique du planning et des affectations</div>
        </div>
        <button class="btn-verifie" onclick="window.location.reload()">
            <i class="bi bi-arrow-repeat"></i> Relancer la vérification
        </button>
    </div>
    <div class="kpi-grid">
        <div class="kpi-card"><div class="kpi-icon red"><i class="bi bi-x-octagon-fill"></i></div><div><div class="kpi-label">Erreurs critiques</div><div class="kpi-value"><?= count($erreursCritiques) ?></div></div></div>
        <div class="kpi-card"><div class="kpi-icon orange"><i class="bi bi-exclamation-triangle-fill"></i></div><div><div class="kpi-label">Avertissements</div><div class="kpi-value"><?= count($avertissements) ?></div></div></div>
        <div class="kpi-card"><div class="kpi-icon green"><i class="bi bi-check-circle-fill"></i></div><div><div class="kpi-label">Contraintes OK</div><div class="kpi-value"><?= $nbOk ?></div></div></div>
        <div class="kpi-card"><div class="kpi-icon blue"><i class="bi bi-list-check"></i></div><div><div class="kpi-label">Total vérifications</div><div class="kpi-value"><?= $totalErreurs + $nbOk ?></div></div></div>
    </div>
    <div class="charts-grid">
        <div class="chart-card">
            <div class="chart-title"><i class="bi bi-pie-chart-fill"></i> Types d'anomalies</div>
            <canvas id="chartAnomalies" height="160"></canvas>
        </div>
        <div class="chart-card">
            <div class="chart-title"><i class="bi bi-bar-chart-fill"></i> Encadrement par prof</div>
            <canvas id="chartEncadr" height="160"></canvas>
        </div>
    </div>

    <?php if (!empty($erreursCritiques)): ?>
    <div class="anomalie-section section-rouge">
        <div class="anomalie-header">
            <div class="anomalie-header-title"><i class="bi bi-x-octagon-fill" style="color:var(--danger)"></i> Erreurs critiques — à corriger immédiatement</div>
            <span class="badge-count rouge"><?= count($erreursCritiques) ?></span>
        </div>
        <ul class="anomalie-list rouge">
            <?php foreach ($erreursCritiques as $err): ?>
            <li class="anomalie-item"><i class="bi bi-x-circle-fill"></i><?= htmlspecialchars($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <?php if (!empty($avertissements)): ?>
    <div class="anomalie-section section-orange">
        <div class="anomalie-header">
            <div class="anomalie-header-title"><i class="bi bi-exclamation-triangle-fill" style="color:var(--warning)"></i> Avertissements — à vérifier</div>
            <span class="badge-count orange"><?= count($avertissements) ?></span>
        </div>
        <ul class="anomalie-list orange">
            <?php foreach ($avertissements as $avert): ?>
            <li class="anomalie-item"><i class="bi bi-exclamation-circle"></i><?= htmlspecialchars($avert) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <?php if (!empty($infos)): ?>
    <div class="anomalie-section section-bleu">
        <div class="anomalie-header">
            <div class="anomalie-header-title"><i class="bi bi-check-circle-fill" style="color:var(--secondary)"></i> Contraintes vérifiées avec succès</div>
            <span class="badge-count vert"><?= count($infos) ?></span>
        </div>
        <ul class="anomalie-list bleu">
            <?php foreach ($infos as $info): ?>
            <li class="anomalie-item"><i class="bi bi-check2-circle" style="color:var(--secondary) !important"></i><?= htmlspecialchars($info) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <?php if ($totalErreurs === 0): ?>
    <div class="alert-ok">
        <i class="bi bi-shield-fill-check" style="font-size:1.5rem"></i>
        <div><strong>Aucune anomalie détectée.</strong><br><span style="font-size:.88rem">Toutes les contraintes sont respectées.</span></div>
    </div>
    <?php endif; ?>
</div>

<script>
<?php if (!empty($typesAnomalies)): ?>
new Chart(document.getElementById('chartAnomalies'),{type:'doughnut',data:{labels:<?= json_encode(array_keys($typesAnomalies)) ?>,datasets:[{data:<?= json_encode(array_values($typesAnomalies)) ?>,backgroundColor:['#e02424','#ff8c00','#1a56db','#7e3af2'],borderWidth:0,hoverOffset:6}]},options:{cutout:'60%',responsive:true,plugins:{legend:{position:'bottom'}}}});
<?php else: ?>
document.getElementById('chartAnomalies').parentElement.innerHTML='<div style="text-align:center;padding:2rem;color:#0e9f6e"><i class="bi bi-shield-check" style="font-size:2rem"></i><br>Aucune anomalie</div>';
<?php endif; ?>
const avg=<?= !empty($encadrStats) ? array_sum($encadrStats)/count($encadrStats) : 0 ?>;
const vals=<?= json_encode(array_values($encadrStats)) ?>;
const bgColors=vals.map(v=>(v>avg+1||v<avg-1)?'#e02424':'#0e9f6e');
new Chart(document.getElementById('chartEncadr'),{type:'bar',data:{labels:<?= json_encode(array_keys($encadrStats)) ?>,datasets:[{label:'Étudiants encadrés',data:vals,backgroundColor:bgColors,borderRadius:7,borderSkipped:false},{type:'line',label:'Moyenne',data:Array(vals.length).fill(avg),borderColor:'#1a56db',borderDash:[6,4],pointRadius:0,borderWidth:2}]},options:{responsive:true,plugins:{legend:{position:'bottom'}},scales:{y:{beginAtZero:true},x:{grid:{display:false}}}}});
</script>
</body>
</html>