<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">
    <title>Dashboard PFE</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>

<body class="bg-light">

<div class="container py-5">

    <h1 class="text-center mb-5">
        Dashboard - Gestion des PFE
    </h1>

    <?php

    // DONNÉES MOCK

    $nbEtudiants = 120;
    $nbProfs = 22;
    $nbSoutenances = 77;

    $repartition = [
        "GI" => 40,
        "ID" => 39,
        "TDAI" => 26,
        "Mathématique" => 15
    ];

    ?>

    <!-- CARDS -->

    <div class="row g-4">

        <div class="col-md-4">

            <div class="card shadow border-0">

                <div class="card-body text-center">

                    <h5>Étudiants</h5>

                    <h1 class="text-primary">
                        <?= $nbEtudiants ?>
                    </h1>

                </div>

            </div>

        </div>

        <div class="col-md-4">

            <div class="card shadow border-0">

                <div class="card-body text-center">

                    <h5>Professeurs</h5>

                    <h1 class="text-success">
                        <?= $nbProfs ?>
                    </h1>

                </div>

            </div>

        </div>

        <div class="col-md-4">

            <div class="card shadow border-0">

                <div class="card-body text-center">

                    <h5>Soutenances</h5>

                    <h1 class="text-danger">
                        <?= $nbSoutenances ?>
                    </h1>

                </div>

            </div>

        </div>

    </div>

    <!-- GRAPHIQUE -->

    <div class="card shadow border-0 mt-5">

        <div class="card-body">

            <h4 class="mb-4">
                Répartition des étudiants par filière
            </h4>

            <canvas id="myChart"></canvas>

        </div>

    </div>

</div>

<script>

const ctx = document.getElementById('myChart');

new Chart(ctx, {

    type: 'bar',

    data: {

        labels: <?= json_encode(array_keys($repartition)) ?>,

        datasets: [{

            label: 'Nombre d\'étudiants',

            data: <?= json_encode(array_values($repartition)) ?>,

            borderWidth: 1

        }]
    },

    options: {

        responsive: true,

        scales: {

            y: {

                beginAtZero: true

            }
        }
    }
});

</script>

</body>
</html>