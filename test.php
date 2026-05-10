<?php

require_once "app/services/VerificationService.php";

$verification = new VerificationService();

$soutenances = [

    [
        'date' => '2025-06-01',
        'heure' => '09:00',
        'professeur' => 'Prof Ahmed'
    ],

    [
        'date' => '2025-06-01',
        'heure' => '10:00',
        'professeur' => 'Prof Ahmed'
    ]
];

$result = $verification->checkRestTime($soutenances);

print_r($result);