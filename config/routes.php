<?php
use App\Controllers\AffectationCtrl;

$router->get('/affecter', function() {
    $ctrl = new AffectationCtrl();
    $ctrl->lancer();
})
?>