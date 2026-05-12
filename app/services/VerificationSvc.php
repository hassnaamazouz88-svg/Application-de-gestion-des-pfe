<?php
namespace App\Services;

use App\Utils\ConstraintChecker;
use App\Models\Professeur;

class VerificationSvc
{
    private ConstraintChecker $checker;

    public function __construct()
    {
        $db            = Professeur::getDB();
        $this->checker = new ConstraintChecker($db);
    }

    /**
     * Retourne toutes les anomalies groupées par type.
     */
    public function getAnomalies(): array
    {
        $anomalies = $this->checker->verifierTout();

        // Grouper par type pour l'affichage
        $groupes = [];
        foreach ($anomalies as $anomalie) {
            $groupes[$anomalie['type']][] = $anomalie;
        }

        return [
            'total'   => count($anomalies),
            'groupes' => $groupes,
            'propre'  => empty($anomalies),  // true = aucune anomalie
        ];
    }
}