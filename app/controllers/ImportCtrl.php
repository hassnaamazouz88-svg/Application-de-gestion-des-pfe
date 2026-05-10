<?php

namespace App\Controllers;

use App\Services\ExcelImportSvc;
use Exception;

class ImportCtrl
{
    private ExcelImportSvc $excelService;

    public function __construct()
    {
        $this->excelService = new ExcelImportSvc();
    }

    public function index()
    {
        // On affiche la vue du formulaire d'import
        require_once __DIR__ . '/../../views/import.view.php';
    }

    public function importEtudiants()
    {
        try {
            $this->validateUpload($_FILES['excel']);

            $this->excelService->importEtudiants($_FILES['excel']);

            // Idéalement, rediriger avec un message de succès
            echo "Succès : L'importation des étudiants a été effectuée.";
            
        } catch (Exception $e) {
            die("Erreur : " . $e->getMessage());
        }
    }

    public function importProfesseurs()
    {
        try {
            $this->validateUpload($_FILES['excel']);

            $this->excelService->importProfesseurs($_FILES['excel']);

            echo "Succès : L'importation des professeurs a été effectuée.";

        } catch (Exception $e) {
            die("Erreur : " . $e->getMessage());
        }
    }

    /**
     * Petite méthode utilitaire pour valider l'upload
     */
    private function validateUpload($file)
    {
        if (!isset($file) || $file['error'] !== 0) {
            throw new Exception("Erreur lors de l'upload du fichier.");
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $allowed = ['xlsx', 'xls', 'ods'];
        
        if (!in_array(strtolower($extension), $allowed)) {
            throw new Exception("Format de fichier non supporté. Veuillez utiliser un fichier Excel.");
        }
    }
}

?>