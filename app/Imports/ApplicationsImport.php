<?php

namespace App\Imports;

use App\Models\Program;
use App\Models\Application;
use App\Models\FormProgram;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ApplicationsImport implements ToModel, WithHeadingRow
{
    protected $programId;
    protected $status;
    protected $notes;

    protected $rowCount = 0;

    public function __construct($programId, $status, $notes)
    {
        $this->programId = $programId;
        $this->status = $status;
        $this->notes = $notes;
    }

    public function model(array $row)
    {
        $this->rowCount++;
        // Récupérer le formulaire associé au programme
        $formProgram = FormProgram::where('program_id', $this->programId)->first();
        
        if (!$formProgram) {
            throw new \Exception("No form found for program ID: {$this->programId}");
        }

        // Construire form_data dynamiquement
        $formData = [];
        $fields = $formProgram->formFields;
        
        foreach ($fields as $field) {
            $columnName = strtolower(str_replace(' ', '_', $field->label));
            if (isset($row[$columnName])) {
                $formData[$field->id] = [
                    'value' => $row[$columnName],
                    'type' => $field->field_type,
                    'label' => $field->label,
                    'options' => $field->options
                ];
            }
        }

        $program = Program::find($this->programId); 

        $date = now()->format('d-m-Y'); // exemple : 06-06-2025

        $lastApplication = $program->applications()
            ->whereDate('submitted_at', now()->startOfDay())
            ->latest('id') // ou 'submitted_at'
            ->first();

        $lastNumber = 0;

        if ($lastApplication) {
            if (preg_match('/-(\d+)$/', $lastApplication->num_candidat, $matches)) {
                $lastNumber = (int) $matches[1];
            }
        }
        
        $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT); // '0002'
        
        $numCandidat = $program->sigle . '-' . $date . '-' . $newNumber;

        return new Application([
            'num_candidat' => $numCandidat,
            'program_id' => $this->programId,
            'status' => $this->status,
            'submitted_at' => now(),
            'notes' => $this->notes,
            'form_data' => json_encode($formData),
        ]);
    }

    public function getRowCount()
    {
        return $this->rowCount;
    }
}