<?php

namespace App\Resources\Admin;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Program;
use App\Models\Document;
use App\Models\Evaluation;
use App\Models\Application;
use App\Resources\Resource;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\EvaluationCriteriaItem;
use App\Resources\Builders\FormBuilder;
use App\Resources\Builders\TableBuilder;
use App\Resources\Concerns\HasResourceData;

class ApplicationResource extends Resource
{
    use HasResourceData;
    
    protected static string $model = Application::class;
    protected static string $panel = 'admin';
    public static string $label = 'Candidats';

    
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
            ->with([
                'user',
                'program.evaluationCriteria.evaluationCriteriaItems'
            ])
            ->column('num_candidat', 'N° candidat')
            ->column('user.name', 'Nom candidat')
            ->column('program.title', 'Programme')
            ->column('submitted_at', 'Date soumission')
            ->column('status', 'Status')
            ->columnCallback('checked_items', 'Critère de sélection', function ($application) {
                $items = optional(optional($application->program)->evaluationCriteria)->evaluationCriteriaItems ?? collect();
    
                $total = $items->count();
                $checked = $items->where('is_checked', true)->count();
    
                return "$checked / $total";
            })
            ->make();
    
        return Inertia::render(static::getComponentPath('index'), [
            'table' => $table,
            'resource' => static::getResourceData(),
        ]);
    }
    
    public function showCustomPage(string $panel, string $page)
    {
        $table = (new TableBuilder(static::$model))
            ->query(function ($query) {
                $query->where('status', 'approved');
            })
            ->with([
                'user',
                'program' => function($query) {
                    $query->with([
                        'grilleLabels.grilleItems',
                        'evaluationCriteria.evaluationCriteriaItems',
                        'region'
                    ]);
                }
            ])
            ->column('num_candidat', 'N° candidat')
            ->column('user.name', 'Nom candidat')
            ->column('program.title', 'Programme')
            ->column('submitted_at', 'Date soumission')
            ->make();
    
        return Inertia::render("{$panel}/Pages/" . Str::studly($page), [
            'table' => $table,
            'resource' => static::getResourceData(),
        ]);
    }


    public static function show($id): \Inertia\Response
    {
        $application = static::$model::with([
            'user',
            'program.evaluationCriteria.evaluationCriteriaItems',
        ])->findOrFail($id);
    
        return Inertia::render(static::getComponentPath('show'), [
            'application' => $application,
            'resource' => static::getResourceData($application),
            'evaluationcriteria' => $application->program->evaluationCriteria ?? null,
        ]);
    }

    public function toggle(Request $request, $id)
    {
        $item = EvaluationCriteriaItem::findOrFail($id);
        $item->is_checked = $request->boolean('is_checked');
        $item->save();

        return redirect()->back()->with('success', 'Mise à jour effectuée.');

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
            // 'user_id' => 'required|exists:users,id',
            // 'program_id' => 'required|exists:programs,id',
            // 'submitted_at' => 'string|required',
            'status' => 'string|required',
            // 'notes' => 'string|required',
            // 'form_data' => 'string|required',
            
        ]);
    
        $application->update($data);
    
        return redirect()->route(static::getRouteName('show'), $id);

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