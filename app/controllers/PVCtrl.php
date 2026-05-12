<?php
namespace App\Controllers;

use App\Services\PVGeneratorSvc;

class PVCtrl
{
    public function generatePlanningPDF()
    {
        $generator = new PVGeneratorSvc();
        $generator->generatePlanning();
    }

    public function generateAffectationPDF()
    {
        $generator = new PVGeneratorSvc();
        $generator->generateAffectation();
    }

    public function generatePV(int $idStnc)
    {
        $generator = new PVGeneratorSvc();
        $generator->generatePV($idStnc);
    }
}