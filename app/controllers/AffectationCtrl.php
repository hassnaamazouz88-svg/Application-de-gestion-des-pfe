<?php
namespace App\Controllers;

use App\Services\AffectationSvc;

class AffectationnCtrl{

    public  function lancer(){
        $service = new AffectationSvc();
        $service->affecter();
        echo "Affectation réussie !";
    }
}
?>
