<?php
use App\Controllers\AffectationnCtrl;

$router->get('/affecter', function() {
    $ctrl = new AffectationnCtrl();
    $ctrl->lancer();
})
?>