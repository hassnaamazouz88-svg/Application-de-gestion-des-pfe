<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Dashboard PFE</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">

    <h1 class="mb-4 text-center">Dashboard - Gestion des PFE</h1>

    <?php
        // DONNÉES MOCK (plus tard viendront de la DB)
        $nbEtudiants = 120;
        $nbProfs = 22;
        $nbSoutenances = 60;

        $repartition = [
            "Informatique" => 70,
            "Mathématique" => 30,
            "Langue" => 20
        ];
    ?>

    <!-- STATISTIQUES -->
    <div class="row">

        <div class="col-md-4">
            <div class="card shadow-sm text-center">
                <div class="card-body">
                    <h5>Étudiants</h5>
                    <h2 class="text-primary"><?= $nbEtudiants ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm text-center">
                <div class="card-body">
                    <h5>Professeurs</h5>
                    <h2 class="text-success"><?= $nbProfs ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm text-center">
                <div class="card-body">
                    <h5>Soutenances</h5>
                    <h2 class="text-danger"><?= $nbSoutenances ?></h2>
                </div>
            </div>
        </div>

    </div>

    <!-- RÉPARTITION -->
    <div class="mt-5">

        <h3>Répartition par filière</h3>

        <table class="table table-bordered mt-3">
            <thead class="table-dark">
                <tr>
                    <th>Filière</th>
                    <th>Nombre d'étudiants</th>
                </tr>
            </thead>

            <tbody>

            <?php foreach ($repartition as $filiere => $nombre): ?>

                <tr>
                    <td><?= $filiere ?></td>
                    <td><?= $nombre ?></td>
                </tr>

            <?php endforeach; ?>

            </tbody>

        </table>

    </div>

</div>

</body>
</html>