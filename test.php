<?php

require_once "app/services/VerificationService.php";

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
        'salle' => 'B2',
        'professeur' => 'Prof Ahmed'
    ]
];

$result = $verification->checkProfConflict($soutenances);

print_r($result);