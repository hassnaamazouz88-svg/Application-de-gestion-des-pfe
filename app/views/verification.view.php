<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Vérification des contraintes</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

</head>

<body class="bg-light">

    <div class="container mt-5">

        <h1 class="text-center mb-5">
            Vérification des contraintes
        </h1>

        <!-- CARD STATISTIQUES -->

        <div class="row mb-4">

            <div class="col-md-4">

                <div class="card shadow-sm">

                    <div class="card-body text-center">

                        <h5>
                            Nombre anomalies
                        </h5>

                        <h2 class="text-danger">

                            <?= count($errors) ?>

                        </h2>

                    </div>

                </div>

            </div>

        </div>

        <!-- ALERT SI AUCUNE ERREUR -->

        <?php if (empty($errors)) : ?>

            <div class="alert alert-success">

                Aucune anomalie détectée.

            </div>

        <?php else : ?>

            <!-- TABLEAU DES ERREURS -->

            <div class="card shadow-sm">

                <div class="card-header bg-danger text-white">

                    Liste des anomalies détectées

                </div>

                <div class="card-body">

                    <table class="table table-bordered">

                        <thead>

                            <tr>

                                <th>#</th>

                                <th>Anomalie</th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php foreach ($errors as $index => $error) : ?>

                                <tr>

                                    <td>
                                        <?= $index + 1 ?>
                                    </td>

                                    <td>
                                        <?= $error ?>
                                    </td>

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