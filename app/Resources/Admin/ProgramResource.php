<?php

namespace App\Resources\Admin;

use App\Models\Program;
use Inertia\Inertia;
use App\Resources\Resource;
use App\Resources\Builders\FormBuilder;
use App\Resources\Builders\TableBuilder;
use App\Resources\Concerns\HasResourceData;
use App\Models\User;
use App\Models\Application;
use App\Models\EvaluationCriteria;
use App\Models\FormField;
use App\Models\Region;

class ProgramResource extends Resource
{
    use HasResourceData;
    
    protected static string $model = Program::class;
    protected static string $panel = 'admin';
    protected static string $label = 'Program';

    
    public static function userOptions(): array
    {
        return User::query()
            ->select(['id', 'name'])
            ->get()
            ->pluck('name', 'id')
            ->toArray();
    }

    public static function applicationsOptions(): array
    {
        return Application::query()
            ->select(['id', 'name'])
            ->get()
            ->pluck('name', 'id')
            ->toArray();
    }

    public static function evaluationCriteriaOptions(): array
    {
        return EvaluationCriteria::query()
            ->select(['id', 'name'])
            ->get()
            ->pluck('name', 'id')
            ->toArray();
    }

    public static function formFieldsOptions(): array
    {
        return FormField::query()
            ->select(['id', 'name'])
            ->get()
            ->pluck('name', 'id')
            ->toArray();
    }

    public static function regionOptions(): array
    {
        return Region::query()
            ->select(['id', 'name'])
            ->get()
            ->pluck('name', 'id')
            ->toArray();
    }


    public static function index(): \Inertia\Response
    {
        $table = (new TableBuilder(static::$model))
        ->column('title', 'Title')
        // ->column('description', 'Description')
        ->column('region.name', 'Region')
        ->column('start_date', 'Start Date')
        ->column('end_date', 'End Date')
        ->column('status', 'Status')
        ->column('user.name', 'User')
        ->make();

        
        return Inertia::render(static::getComponentPath('index'), [
            'table' => $table,
            'resource' => static::getResourceData(),
        ]);
    }

    public static function show($id): \Inertia\Response
    {
        $program = static::$model::with([
            'user',
            'region',
            'user',
        ])->findOrFail($id);
    
        
        return Inertia::render(static::getComponentPath('show'), [
            'program' => $program,
            'resource' => static::getResourceData($program),
        ]);
    }
    
    public static function create(): \Inertia\Response
    {
        $form = (new FormBuilder())
        ->field('title', 'text', ['required' => true])
        ->field('description', 'textarea', ['required' => true])
        ->field('region_id', 'select', [
                        'options' => Region::pluck('name', 'id'),
                        'required' => true
                    ])
        ->field('start_date', 'date', ['required' => true])
        ->field('end_date', 'date', ['required' => true])
        ->field('status', 'select', 
        [
                'options' => [
                    'draft' => 'draft',
                    'published' => 'published',
                    'archived' => 'archived',
                ],
                'required' => true
            ])
        ->field('user_id', 'select', [
                        'options' => User::pluck('name', 'id'),
                        'required' => true
                    ])
        ->make();

        return Inertia::render(static::getComponentPath('create'), [
            'form' => $form,
            'resource' => static::getResourceData(),
        ]);
    }

    public static function store(): \Illuminate\Http\RedirectResponse
    {
        $data = request()->validate([
            'title' => 'string|required',
            'description' => 'string|required',
            'region_id' => 'required|exists:regions,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'status' => 'string|required',
            'user_id' => 'required|exists:users,id',
            
        ]);

        static::$model::create($data);

        return redirect()->route(static::getRouteName('index'));
    }

    public static function edit($id): \Inertia\Response
    {
        $program = static::$model::findOrFail($id);

        $form = (new FormBuilder())
        ->field('title', 'text', [
                        'required' => true,
                        'value' => $program->title
                    ])
        ->field('description', 'textarea', [
                        'required' => true,
                        'value' => $program->description
                    ])
        ->field('region_id', 'select', [
                        'options' => \App\Models\Region::pluck('name', 'id'),
                        'value' => $program->region_id,
                        'required' => true
                    ])
        ->field('start_date', 'date', [
                        'required' => true,
                        'value' => $program->start_date
                    ])
        ->field('end_date', 'date', [
                        'required' => true,
                        'value' => $program->end_date
                    ])
        ->field('status', 'select', [
                        'required' => true,
                        'value' => $program->status,
                        'options' => [
                            'draft' => 'draft',
                            'published' => 'published',
                            'archived' => 'archived',
                        ]
                    ])
        ->field('user_id', 'select', [
                        'options' => User::pluck('name', 'id'),
                        'value' => $program->user_id,
                        'required' => true
                    ])
        ->make();

        return Inertia::render(static::getComponentPath('edit'), [
            'program' => $program,
            'form' => $form,
            'resource' => static::getResourceData($program),
        ]);
    }

    

    public static function update($id): \Illuminate\Http\RedirectResponse
    {
        $program = static::$model::findOrFail($id);
    
        $data = request()->validate([
            'title' => 'string|required',
            'description' => 'string|required',
            'region_id' => 'required|exists:regions,id',
            'start_date' => 'date|required',
            'end_date' => 'date|required',
            'status' => 'string|required',
            'user_id' => 'required|exists:users,id',
            
        ]);
    
        $program->update($data);
    
        return redirect()->route(static::getRouteName('index'));
    }

    public static function destroy($id): \Illuminate\Http\RedirectResponse
    {
        $program = static::$model::findOrFail($id);
        $program->delete();
        return redirect()->route(static::getRouteName('index'));
    }

    protected static function getComponentPath(string $view): string
    {
        return static::$panel . '/Resources/' . class_basename(static::class) . '/' . $view;
    }
}