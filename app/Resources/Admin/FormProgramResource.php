<?php

namespace App\Resources\Admin;

use App\Models\FormProgram;
use Inertia\Inertia;
use App\Resources\Resource;
use App\Resources\Builders\FormBuilder;
use App\Resources\Builders\TableBuilder;
use App\Resources\Concerns\HasResourceData;
use App\Models\Program;

class FormProgramResource extends Resource
{
    use HasResourceData;
    
    protected static string $model = FormProgram::class;
    protected static string $panel = 'admin';
    protected static string $label = 'Form Program';

    
    public static function programOptions(): array
    {
        return Program::query()
            ->select(['title', 'name'])
            ->get()
            ->pluck('title', 'id')
            ->toArray();
    }


    public static function index(): \Inertia\Response
    {
        $table = (new TableBuilder(FormProgram::class))
        ->column('name', 'Nom')
        ->column('program.title', 'Programme')
        ->make();

        // dd($table);

        return Inertia::render(static::getComponentPath('index'), [
            'table' => $table,
            'resource' => static::getResourceData(),
        ]);
    }

    public static function show($id): \Inertia\Response
    {
        $formprogram = static::$model::with('formFields', 'program')->findOrFail($id);

        return Inertia::render(static::getComponentPath('show'), [
            'formprogram' => $formprogram,
            'formFields' => $formprogram->formFields, // <-- Ajoute ceci
            'resource' => static::getResourceData($formprogram),
        ]);
    }

    
    public static function create(): \Inertia\Response
    {
        $form = (new FormBuilder())
        ->field('name', 'text', ['required' => true])
        ->field('program_id', 'select', [
                        'options' => Program::pluck('title', 'id'),
                        'required' => true
                    ])
        ->make();

        return Inertia::render(static::getComponentPath('create'), [
            'form' => $form,
            'resource' => static::getResourceData(),
        ]);
    }

    // public static function store(): \Illuminate\Http\RedirectResponse
    // {
    //     $data = request()->validate([
    //         'name' => 'string|required',
    //         'program_id' => 'required|exists:programs,id',
            
    //     ]);

    //     static::$model::create($data);

    //     return redirect()->route(static::getRouteName('index'));
    // }

    public static function store(): \Illuminate\Http\RedirectResponse
    {
        //dd(request());
        $data = request()->validate([
            'name' => 'required|string',
            'program_id' => 'required|exists:programs,id',
            'form_fields' => 'nullable|array',
            'form_fields.*.label' => 'required|string',
            'form_fields.*.field_type' => 'required|string|in:text,textarea,select,checkbox,radio',
            'form_fields.*.required' => 'boolean',
            'form_fields.*.options' => 'nullable|string',
        ]);

        
    
        // Création du FormProgram
        $formProgram = static::$model::create([
            'name' => $data['name'],
            'program_id' => $data['program_id'],
        ]);
    
        // Enregistrement des champs seulement si le FormProgram a été créé
        if ($formProgram->exists && isset($data['form_fields'])) {
            foreach ($data['form_fields'] as $fieldData) {
                $options = null;
                
                if (in_array($fieldData['field_type'], ['select', 'checkbox', 'radio']) && !empty($fieldData['options'])) {
                    $options = json_encode(
                        array_filter(
                            array_map('trim', explode("\n", $fieldData['options']))
                        ));
                }
                $formProgram->formFields()->create([
                    'label' => $fieldData['label'],
                    'field_type' => $fieldData['field_type'],
                    'required' => $fieldData['required'] ?? false,
                    'options' => $options,
                ]);
            }
        }
    
        return redirect()->route(static::getRouteName('index'));
    }

    public static function edit($id): \Inertia\Response
    {
        $formProgram = static::$model::with('formFields')->findOrFail($id);

        $form = (new FormBuilder())
            ->field('name', 'text', [
                'required' => true,
                'value' => $formProgram->name
            ])
            ->field('program_id', 'select', [
                'options' => Program::pluck('title', 'id'),
                'value' => $formProgram->program_id,
                'required' => true
            ])
            ->make();

        return Inertia::render(static::getComponentPath('edit'), [
            'formProgram' => $formProgram,
            'form' => $form,
            'resource' => static::getResourceData($formProgram),
            'formFields' => $formProgram->formFields->map(function ($field) {
                return [
                    'id' => $field->id,
                    'label' => $field->label,
                    'field_type' => $field->field_type,
                    'required' => $field->required,
                    'options' => $field->options 
                ];
            })->toArray()
        ]);
    }

    public static function update($id): \Illuminate\Http\RedirectResponse
    {
        $data = request()->validate([
            'name' => 'required|string',
            'program_id' => 'required|exists:programs,id',
            'form_fields' => 'nullable|array',
            'form_fields.*.id' => 'nullable|exists:form_fields,id',
            'form_fields.*.label' => 'required|string',
            'form_fields.*.field_type' => 'required|string|in:text,textarea,select,checkbox,radio',
            'form_fields.*.required' => 'boolean',
            'form_fields.*.options' => 'nullable|string',
        ]);
    
        $formProgram = static::$model::findOrFail($id);
        $formProgram->update([
            'name' => $data['name'],
            'program_id' => $data['program_id'],
        ]);
    
        // IDs des champs existants pour détecter les suppressions
        $existingFieldIds = $formProgram->formFields()->pluck('id')->toArray();
        $submittedFieldIds = [];
    
        // Mise à jour ou création des champs
        foreach ($data['form_fields'] ?? [] as $fieldData) {
            $options = null;
            
            if (in_array($fieldData['field_type'], ['select', 'checkbox', 'radio'])) {
                $options = !empty($fieldData['options']) 
                    ? json_encode(array_filter(array_map('trim', explode("\n", $fieldData['options']))))
                    : null;
            }
    
            if (isset($fieldData['id'])) {
                // Mise à jour d'un champ existant
                $formProgram->formFields()->where('id', $fieldData['id'])->update([
                    'label' => $fieldData['label'],
                    'field_type' => $fieldData['field_type'],
                    'required' => $fieldData['required'] ?? false,
                    'options' => $options,
                ]);
                $submittedFieldIds[] = $fieldData['id'];
            } else {
                // Création d'un nouveau champ
                $formProgram->formFields()->create([
                    'label' => $fieldData['label'],
                    'field_type' => $fieldData['field_type'],
                    'required' => $fieldData['required'] ?? false,
                    'options' => $options,
                ]);
            }
        }
    
        // Suppression des champs non soumis
        $fieldsToDelete = array_diff($existingFieldIds, $submittedFieldIds);
        if (!empty($fieldsToDelete)) {
            $formProgram->formFields()->whereIn('id', $fieldsToDelete)->delete();
        }
    
        return redirect()->route(static::getRouteName('index'));
    }

    public static function destroy($id): \Illuminate\Http\RedirectResponse
    {
        $formprogram = static::$model::findOrFail($id);
        $formprogram->delete();
        return redirect()->route(static::getRouteName('index'));
    }

    protected static function getComponentPath(string $view): string
    {
        return static::$panel . '/Resources/' . class_basename(static::class) . '/' . $view;
    }
}