<?php

require_once "app/services/VerificationService.php";

$verification = new VerificationService();

$affectations = [

    "Prof Ahmed" => 7,
    "Prof Sara" => 2,
    "Prof Karim" => 4,
    "Prof Amal" => 3
];

$result = $verification->checkBalancedAssignments($affectations);

print_r($result);