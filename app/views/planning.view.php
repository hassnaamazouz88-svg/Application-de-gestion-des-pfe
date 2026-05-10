<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Planning des soutenances</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">

    <h1 class="text-center mb-4">Planning des soutenances</h1>

    <?php
        // DONNÉES MOCK
        $planning = [

            [
                "etudiant" => "Ali",
                "salle" => "A1",
                "heure" => "09:00 - 10:00",
                "jury" => "Prof Ahmed / Prof Sara"
            ],

            [
                "etudiant" => "Sara",
                "salle" => "B2",
                "heure" => "10:00 - 11:00",
                "jury" => "Prof Karim / Prof Amal"
            ],

            [
                "etudiant" => "Youssef",
                "salle" => "A1",
                "heure" => "11:00 - 12:00",
                "jury" => "Prof Ahmed / Prof Ali"
            ]
        ];
    ?>

    <table class="table table-bordered table-hover">

        <thead class="table-dark">
            <tr>
                <th>Étudiant</th>
                <th>Salle</th>
                <th>Horaire</th>
                <th>Jury</th>
            </tr>
        </thead>

        <tbody>

        <?php foreach ($planning as $p): ?>

            <tr>
                <td><?= $p["etudiant"] ?></td>
                <td><?= $p["salle"] ?></td>
                <td><?= $p["heure"] ?></td>
                <td><?= $p["jury"] ?></td>
            </tr>

        <?php endforeach; ?>

        </tbody>

    </table>

</div>

</body>
</html>