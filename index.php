<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/routes.php';

use App\Controllers\AffectationCtrl;

$ctrl = new AffectationCtrl();

$ctrl->lancer();

?>