<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\Etudiant;
use App\Models\Professeur;
use Exception;

class ExcelImportSvc
{
    private function extractRows(array $file): array
    {
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            throw new Exception("Fichier temporaire introuvable.");
        }

        $spreadsheet = IOFactory::load($file['tmp_name']);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        array_shift($rows); // Retire les entêtes

        return $rows;
    }

    public function importEtudiants(array $file): void
    {
        $rows = $this->extractRows($file);

        foreach ($rows as $row) {
            if (empty($row[0])) continue;

            $data = [
                'nom'          => trim($row[0]),
                'prenom'       => trim($row[1]),
                'email'        => trim($row[2]),
                'filiere'      => trim($row[3]),
                'sujet_pfe'    => trim($row[4]),
                'langue_pfe'   => trim($row[5])
            ];

            Etudiant::create($data);
        }
    }

    public function importProfesseurs(array $file): void
    {
        $rows = $this->extractRows($file);

        foreach ($rows as $row) {
            if (empty($row[0])) continue;

            $data = [
                'nom'        => trim($row[0]),
                'prenom'     => trim($row[1]),
                'email'      => trim($row[2]),
                'specialite' => trim($row[3]) // Mis à jour : 'specialite' au lieu de 'departement'
            ];

            Professeur::create($data);
        }
    }
}

?>