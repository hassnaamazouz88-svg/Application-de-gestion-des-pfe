<?php

class VerificationService
{

    public function checkSalleConflict($soutenances)
    {
        $errors = [];

        for ($i = 0; $i < count($soutenances); $i++) {

            for ($j = $i + 1; $j < count($soutenances); $j++) {

                if (
                    $soutenances[$i]['date'] == $soutenances[$j]['date']
                    &&
                    $soutenances[$i]['heure'] == $soutenances[$j]['heure']
                    &&
                    $soutenances[$i]['salle'] == $soutenances[$j]['salle']
                ) {

                    $errors[] =
                        "Conflit détecté dans la salle "
                        . $soutenances[$i]['salle'];
                }
            }
        }

        return $errors;
    }



    public function checkProfConflict($soutenances)
    {
        $errors = [];

        for ($i = 0; $i < count($soutenances); $i++) {

            for ($j = $i + 1; $j < count($soutenances); $j++) {

                if (
                    $soutenances[$i]['date'] == $soutenances[$j]['date']
                    &&
                    $soutenances[$i]['heure'] == $soutenances[$j]['heure']
                    &&
                    $soutenances[$i]['professeur'] == $soutenances[$j]['professeur']
                ) {

                    $errors[] =
                        "Conflit professeur : "
                        . $soutenances[$i]['professeur'];
                }
            }
        }

        return $errors;
    }

}