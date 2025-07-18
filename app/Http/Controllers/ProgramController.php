<?php
namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Program;
use App\Models\Document;
use Illuminate\Http\Request;
use App\Models\EvaluationCriteria;

class ProgramController extends Controller
{
    /**
     * Affiche la liste des programmes disponibles.
     */
    
    public function index(Request $request)
    {
        $query = Program::query();
    
        // 🔍 Recherche (sur le titre par exemple)
        if ($search = $request->input('search')) {
            $query->where('title', 'like', "%{$search}%");
        }
    
        // 🟦 Exemple de filtre : statut (si tu as une colonne 'status')
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
    
        $programs = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
    
        return Inertia::render('Template/Programs/Index', [
            'programs' => $programs,
            'filters' => $request->only(['search', 'status']),
        ]);
    }
    

    /**
     * Affiche les détails d’un programme spécifique.
     */
  public function show(Program $program)
{
    return Inertia::render('Template/Programs/Show', [
        'program' => $program->only([
            'id', 'title', 'description', 'start_date', 'end_date', 'status'
        ]),
        'critere' => $program->evaluationCriteria,
    ]);
}

    /**
     * Affiche le formulaire de candidature dynamique pour un programme.
     */
    public function apply(Program $program)
    {

        $fields = $program->formProgram->formFields;


        return Inertia::render('Template/Programs/Apply', [
            'program' => $program,
            'fields' => $fields,
        ]);
    }

    /**
     * Traitement de la soumission de candidature.
     */
    public function submit(Request $request, Program $program)
    {
        $fields = $program->formProgram->formFields;
        
        // dd($request);
        // Préparation des règles de validation
        $validationRules = [];
        $customMessages = [];
        $customAttributes = [];

        foreach ($fields as $field) {
            $fieldName = $field->id;
            $rules = [];
            
            // Règle required
            if ($field->required) {
                $rules[] = 'required';
                $customMessages["$fieldName.required"] = "Le champ {$field->label} est obligatoire.";
            } else {
                $rules[] = 'nullable';
            }

            // Règles spécifiques par type de champ
            switch ($field->field_type) {
                case 'email':
                    $rules[] = 'email';
                    $customMessages += [
                        "$fieldName.email" => "Veuillez entrer une adresse email valide.",
                        "$fieldName.max" => "L'email ne doit pas dépasser 255 caractères."
                    ];
                    break;

                case 'number':
                    $rules[] = 'numeric';
                    $customMessages["$fieldName.numeric"] = "Veuillez entrer un nombre valide.";
                    
                    if (isset($field->min)) {
                        $rules[] = 'min:'.$field->min;
                        $customMessages["$fieldName.min"] = "La valeur minimale est {$field->min}.";
                    }
                    if (isset($field->max)) {
                        $rules[] = 'max:'.$field->max;
                        $customMessages["$fieldName.max"] = "La valeur maximale est {$field->max}.";
                    }
                    break;

                case 'file':
                    $rules[] = 'file';
                    $customMessages["$fieldName.file"] = "Veuillez uploader un fichier valide.";
                    
                    if (isset($field->accept)) {
                        $mimes = implode(',', array_map(function($ext) {
                            return str_replace(['.', ' '], '', $ext);
                        }, explode(',', $field->accept)));
                        $rules[] = 'mimes:'.$mimes;
                        $customMessages["$fieldName.mimes"] = "Types de fichiers acceptés: {$field->accept}.";
                    }
                    
                    if (isset($field->max_size)) {
                        $maxSize = $field->max_size * 1024; // Conversion en Ko
                        $rules[] = 'max:'.$maxSize;
                        $customMessages["$fieldName.max"] = "La taille maximale est {$field->max_size} Ko.";
                    }
                    break;

                case 'date':
                    $rules[] = 'date';
                    $customMessages["$fieldName.date"] = "Format de date invalide (AAAA-MM-JJ).";
                    break;

                case 'textarea':
                    $rules[] = 'string';
                    if (isset($field->max_length)) {
                        $rules[] = 'max:'.$field->max_length;
                        $customMessages["$fieldName.max"] = "Maximum {$field->max_length} caractères autorisés.";
                    }
                    break;

                case 'select':
                    if (isset($field->options)) {
                        $options = is_array($field->options) ? $field->options : json_decode($field->options, true);
                        $allowedValues = array_column($options, 'value') ?? $options;
                        $rules[] = 'in:'.implode(',', $allowedValues);
                        $customMessages["$fieldName.in"] = "Veuillez sélectionner une option valide.";
                    }
                    break;

                case 'checkbox':
                    $rules[] = 'boolean';
                    $customMessages["$fieldName.boolean"] = "Valeur invalide pour la case à cocher.";
                    break;

                case 'radio':
                    if (isset($field->options)) {
                        $options = is_array($field->options) ? $field->options : json_decode($field->options, true);
                        // dd($options);
                        $allowedValues =  $options;
                        $rules[] = 'in:'.implode(',', $allowedValues);
                        $customMessages["$fieldName.in"] = "Veuillez sélectionner une option valide.";
                    }
                    break;

                default: // text, tel, url, etc.
                    $rules[] = 'string';
                    if (isset($field->pattern)) {
                        $rules[] = 'regex:'.$field->pattern;
                        $customMessages["$fieldName.regex"] = "Le format saisi est invalide.";
                    }
                    break;
            }

            $validationRules[$fieldName] = $rules;
            $customAttributes[$fieldName] = $field->label;
        }

        // Validation des données
        $validatedData = $request->validate($validationRules, $customMessages, $customAttributes);

        // Préparation des données pour le JSON
        $formData = [];

        foreach ($fields as $field) {
            $fieldName = $field->id;
            $value = $validatedData[$fieldName] ?? null;

            if ($field->field_type === 'file' && $request->hasFile($fieldName)) {
                $file = $request->file($fieldName);
                $path = $file->store(
                    "documents/{$program->title}-{$program->sigle}/{$fieldName}",
                    'public'
                );

                // Document::create([
                //     'application_id' => ,
                //     'label' => "{$program->title}-{$program->sigle}",
                //     'file_path' => $path,
                // ]);

                // $formData[$fieldName] = [
                //     'path' => $path,
                //     'original_name' => $file->getClientOriginalName(),
                //     'mime_type' => $file->getClientMimeType(),
                //     'size' => $file->getSize(),
                //     'uploaded_at' => now()->toDateTimeString()
                // ];
            } else {
                // Pour les autres types de champs
                $formData[$fieldName] = [
                    'value' => $value,
                    'type' => $field->field_type,
                    'label' => $field->label
                ];

                // Ajout des options pour les selects/radios
                if (in_array($field->field_type, ['select', 'radio'])) {
                    $formData[$fieldName]['options'] = is_array($field->options) 
                        ? $field->options 
                        : json_decode($field->options, true);
                }
            }
        }

        // dd($program->sigle.now(). );
        // Enregistrement de la candidature

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
        
        $application = $program->applications()->create([
            'num_candidat' =>  $numCandidat,
            'user_id' => auth()->id(),
            'program_id' => $program->id,
            'form_data' => $formData,
            'submitted_at' => now(),
            'status' => 'pending',
        ]);
        
        if(isset($path)){
            Document::create([
                'application_id' => $application->id,
                'label' => "{$program->title}-{$program->sigle}",
                'file_path' => $path,
            ]);
        }

        
        return redirect()
            ->route('program.show', $program->id)
            ->with('success', 'Votre candidature a été soumise avec succès!');
    }
}
