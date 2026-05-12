<?php
namespace App\Controllers;

use App\Services\VerificationSvc;

class VerifCtrl
{
    public function index()
    {
        $service = new VerificationSvc();

        // Récupérer les soutenances depuis la DB
        $soutenances = $service->getAllSoutenances();

        // Lancer toutes les vérifications
        $erreursCritiques = array_merge(
            $service->checkSalleConflict($soutenances),
            $service->checkProfDoubleAffectation($soutenances)
        );

        $avertissements = array_merge(
            $service->checkReposInsuffisant($soutenances),
            $service->checkEquilibreEncadrement()
        );

        $infos = $service->checkContraintesOK($soutenances);

        $encadrStats  = $service->getEncadrementStats();
        $typesAnomalies = $service->getTypesAnomalies($erreursCritiques, $avertissements);

        $totalErreurs = count($erreursCritiques) + count($avertissements);
        $nbOk         = count($infos);

        require __DIR__ . '/../../views/verification.view.php';
    }
}