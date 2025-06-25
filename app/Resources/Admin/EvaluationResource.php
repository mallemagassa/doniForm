<?php

namespace App\Resources\Admin;

use App\Models\Evaluation;
use Inertia\Inertia;
use App\Resources\Resource;
use App\Resources\Builders\FormBuilder;
use App\Resources\Builders\TableBuilder;
use App\Resources\Concerns\HasResourceData;
use App\Models\Application;
use App\Models\User;
use App\Models\EvaluationCriteria;

class EvaluationResource extends Resource
{
    use HasResourceData;
    
    protected static string $model = Evaluation::class;
    protected static string $panel = 'admin';
    protected static string $label = 'Evaluation';

    
    public static function applicationOptions(): array
    {
        return Application::query()
            ->select(['id', 'notes'])
            ->get()
            ->pluck('notes', 'id')
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

    public static function criterionOptions(): array
    {
        return EvaluationCriteria::query()
            ->select(['id', 'name'])
            ->get()
            ->pluck('name', 'id')
            ->toArray();
    }


    public static function index(): \Inertia\Response
    {
        $table = (new TableBuilder(static::$model))
        ->column('user.name', 'User')
        ->column('application.program.title', 'Application')
        ->column('criterion.label', 'Criterion')
        ->column('score', 'Score')
        // ->column('comment', 'Comment')
        ->make();

        return Inertia::render(static::getComponentPath('index'), [
            'table' => $table,
            'resource' => static::getResourceData(),
        ]);
    }

    public static function show($id): \Inertia\Response
    {
        $evaluation = static::$model::with([
            'user',
            'application',
            'criterion',
        ])->findOrFail($id);
    
        return Inertia::render(static::getComponentPath('show'), [
            'evaluation' => $evaluation,
            'resource' => static::getResourceData($evaluation),
        ]);
    }
    
    public static function create(): \Inertia\Response
    {
        $form = (new FormBuilder())
        ->field('user_id', 'select', [
                        'options' => \App\Models\User::pluck('name', 'id'),
                        'required' => true
                    ])
        ->field('application_id', 'select', [
                        'options' => \App\Models\Application::pluck('notes', 'id'),
                        'required' => true
                    ])
        ->field('criterion_id', 'select', [
                        'options' => \App\Models\EvaluationCriteria::pluck('label', 'id'),
                        'required' => true
                    ])
        ->field('score', 'text', ['required' => true])
        ->field('comment', 'textarea', ['required' => true])
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
            'application_id' => 'required|exists:applications,id',
            'criterion_id' => 'required|exists:evaluation_criterias,id',
            'score' => 'string|required',
            'comment' => 'string|required',
            
        ]);

        static::$model::create($data);

        return redirect()->route(static::getRouteName('index'));
    }

    public static function edit($id): \Inertia\Response
    {
        $evaluation = static::$model::findOrFail($id);

        $form = (new FormBuilder())
        ->field('user_id', 'select', [
                        'options' => \App\Models\User::pluck('name', 'id'),
                        'value' => $evaluation->user_id,
                        'required' => true
                    ])
        ->field('application_id', 'select', [
                        'options' => \App\Models\Application::pluck('name', 'id'),
                        'value' => $evaluation->application_id,
                        'required' => true
                    ])
        ->field('criterion_id', 'select', [
                        'options' => \App\Models\EvaluationCriteria::pluck('name', 'id'),
                        'value' => $evaluation->criterion_id,
                        'required' => true
                    ])
        ->field('score', 'text', [
                        'required' => true,
                        'value' => $evaluation->score
                    ])
        ->field('comment', 'textarea', [
                        'required' => true,
                        'value' => $evaluation->comment
                    ])
        ->make();

        return Inertia::render(static::getComponentPath('edit'), [
            'evaluation' => $evaluation,
            'form' => $form,
            'resource' => static::getResourceData($evaluation),
        ]);
    }

    public static function update($id): \Illuminate\Http\RedirectResponse
    {
        $evaluation = static::$model::findOrFail($id);
    
        $data = request()->validate([
            'user_id' => 'required|exists:users,id',
            'application_id' => 'required|exists:applications,id',
            'criterion_id' => 'required|exists:evaluation_criterias,id',
            'score' => 'string|required',
            'comment' => 'string|required',
            
        ]);
    
        $evaluation->update($data);
    
        return redirect()->route(static::getRouteName('index'));
    }

    public static function destroy($id): \Illuminate\Http\RedirectResponse
    {
        $evaluation = static::$model::findOrFail($id);
        $evaluation->delete();
        return redirect()->route(static::getRouteName('index'));
    }

    protected static function getComponentPath(string $view): string
    {
        return static::$panel . '/Resources/' . class_basename(static::class) . '/' . $view;
    }
}