<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <title>Vérification</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">

<div class="container py-5">

    <h1 class="text-center mb-5">
        Vérification des contraintes
    </h1>

    <?php

    // MOCK DATA

    $errors = [

        "Conflit salle A1 à 09h00",
        "Prof Ahmed affecté à deux soutenances simultanées",
        "Repos insuffisant pour Prof Sara"

    ];

    ?>

    <div class="row mb-4">

        <div class="col-md-4">

            <div class="card shadow border-0">

                <div class="card-body text-center">

                    <h5>Nombre anomalies</h5>

                    <h1 class="text-danger">

                        <?= count($errors) ?>

                    </h1>

                </div>

            </div>

        </div>

    </div>

    <?php if(empty($errors)): ?>

        <div class="alert alert-success">

            Aucune anomalie détectée.

        </div>

    <?php else: ?>

        <div class="card shadow border-0">

            <div class="card-header bg-danger text-white">

                Liste des anomalies

            </div>

            <div class="card-body">

                <table class="table table-bordered">

                    <thead>

                        <tr>

                            <th>#</th>
                            <th>Description</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php foreach($errors as $index => $error): ?>

                        <tr>

                            <td><?= $index + 1 ?></td>

                            <td><?= $error ?></td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        </div>

    <?php endif; ?>

</div>

</body>
</html>