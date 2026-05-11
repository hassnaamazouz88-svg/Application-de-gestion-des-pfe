<?php

require_once __DIR__ . '/../services/VerificationService.php';

class VerificationController
{

    public function index()
    {

        $verification = new VerificationService();

        $soutenances = [

            [
                'date' => '2025-06-01',
                'heure' => '09:00',
                'salle' => 'A1',
                'professeur' => 'Prof Ahmed'
            ],

            [
                'date' => '2025-06-01',
                'heure' => '09:00',
                'salle' => 'A1',
                'professeur' => 'Prof Ahmed'
            ]
        ];

        $errorsSalle =
            $verification->checkSalleConflict($soutenances);

        $errorsProf =
            $verification->checkProfConflict($soutenances);

        $errors = array_merge($errorsSalle, $errorsProf);

        require __DIR__ . '/../views/verification.view.php';
    }
}