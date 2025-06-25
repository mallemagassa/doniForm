<?php

namespace App\Resources\Admin;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Program;
use App\Models\Document;
use App\Models\Evaluation;
use App\Models\Application;
use App\Resources\Resource;
use Illuminate\Support\Facades\Gate;
use App\Resources\Builders\FormBuilder;
use App\Resources\Builders\TableBuilder;
use App\Resources\Concerns\HasResourceData;

class ApplicationResource extends Resource
{
    use HasResourceData;
    
    protected static string $model = Application::class;
    protected static string $panel = 'admin';
    protected static string $label = 'Application';

    
    public static function programOptions(): array
    {
        return Program::query()
            ->select(['id', 'title'])
            ->get()
            ->pluck('title', 'id')
            ->toArray();
    }

    public static function userOptions(): array
    {
        return User::query()
            ->select(['id', 'name'])
            ->get()
            ->pluck('name', 'id')
            ->toArray();
    }

    public static function evaluationsOptions(): array
    {
        return Evaluation::query()
            ->select(['id', 'name'])
            ->get()
            ->pluck('name', 'id')
            ->toArray();
    }

    public static function documentsOptions(): array
    {
        return Document::query()
            ->select(['id', 'name'])
            ->get()
            ->pluck('name', 'id')
            ->toArray();
    }


    public static function index(): \Inertia\Response
    {
        $table = (new TableBuilder(static::$model))
        ->column('user.name', 'User')
        ->column('program.title', 'Program')
        ->column('submitted_at', 'Submitted At')
        ->column('status', 'Status')
        // ->column('notes', 'Notes')
        ->column('form_data', 'Form Data')
        ->make();

        return Inertia::render(static::getComponentPath('index'), [
            'table' => $table,
            'resource' => static::getResourceData(),
        ]);
    }

    public static function show($id): \Inertia\Response
    {
        
        $application = static::$model::with([
            'user', // Charge seulement l'id et le nom de l'utilisateur
            'program', // Charge seulement l'id et le titre du programme
            // Ajoutez d'autres relations si nécessaire
        ])->findOrFail($id);
    
        return Inertia::render(static::getComponentPath('show'), [
            'application' => $application,
            'resource' => static::getResourceData($application),
        ]);
    }
    
    public static function create(): \Inertia\Response
    {
        $form = (new FormBuilder())
        ->field('user_id', 'select', [
                        'options' => User::pluck('name', 'id'),
                        'required' => true
                    ])
        ->field('program_id', 'select', [
                        'options' => Program::pluck('title', 'id'),
                        'required' => true
                    ])
        ->field('submitted_at', 'date', ['required' => true])
        ->field('status', 'select', 
        [
                'options' => [
                    'draft' => 'draft',
                    'submitted' => 'submitted',
                    'validated_provisional' => 'validated_provisional',
                    'validated_final' => 'validated_final',
                ],
                'required' => true
            ])
        ->field('notes', 'textarea', ['required' => true])
        ->field('form_data', 'text', ['required' => true])
        ->make();

        return Inertia::render(static::getComponentPath('create'), [
            'form' => $form,
            'resource' => static::getResourceData(),
        ]);
    }

    public static function store(): \Illuminate\Http\RedirectResponse
    {
        $data = request()->validate([
            'user_id' => 'required|exists:users,id',
            'program_id' => 'required|exists:programs,id',
            'submitted_at' => 'string|required',
            'status' => 'string|required',
            'notes' => 'string|required',
            'form_data' => 'string|required',
            
        ]);

        static::$model::create($data);

        return redirect()->route(static::getRouteName('index'));
    }

    public static function edit($id): \Inertia\Response
    {
        $application = static::$model::findOrFail($id);

        $form = (new FormBuilder())
        ->field('user_id', 'select', [
                        'options' => User::pluck('name', 'id'),
                        'value' => $application->user_id,
                        'required' => true
                    ])
        ->field('program_id', 'select', [
                        'options' => Program::pluck('title', 'id'),
                        'value' => $application->program_id,
                        'required' => true
                    ])
        ->field('submitted_at', 'text', [
                        'required' => true,
                        'value' => $application->submitted_at
                    ])
        ->field('status', 'select', 
        [
                'options' => [
                    'draft' => 'draft',
                    'submitted' => 'submitted',
                    'validated_provisional' => 'validated_provisional',
                    'validated_final' => 'validated_final',
                ],
                'required' => true
            ])
        ->field('notes', 'textarea', [
                        'required' => true,
                        'value' => $application->notes
                    ])
        ->field('form_data', 'text', [
                        'required' => true,
                        'value' => $application->form_data
                    ])
        ->make();

        return Inertia::render(static::getComponentPath('edit'), [
            'application' => $application,
            'form' => $form,
            'resource' => static::getResourceData($application),
        ]);
    }

    public static function update($id): \Illuminate\Http\RedirectResponse
    {
        $application = static::$model::findOrFail($id);
    
        $data = request()->validate([
            'user_id' => 'required|exists:users,id',
            'program_id' => 'required|exists:programs,id',
            'submitted_at' => 'string|required',
            'status' => 'string|required',
            'notes' => 'string|required',
            'form_data' => 'string|required',
            
        ]);
    
        $application->update($data);
    
        return redirect()->route(static::getRouteName('index'));
    }

    public static function destroy($id): \Illuminate\Http\RedirectResponse
    {
        $application = static::$model::findOrFail($id);
        $application->delete();
        return redirect()->route(static::getRouteName('index'));
    }

    protected static function getComponentPath(string $view): string
    {
        return static::$panel . '/Resources/' . class_basename(static::class) . '/' . $view;
    }
}