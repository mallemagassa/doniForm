<?php

namespace App\Resources\Admin;

use App\Models\EvaluationCriteria;
use Inertia\Inertia;
use App\Resources\Resource;
use App\Resources\Builders\FormBuilder;
use App\Resources\Builders\TableBuilder;
use App\Resources\Concerns\HasResourceData;
use App\Models\Program;
use App\Models\Evaluation;

class EvaluationCriteriaResource extends Resource
{
    use HasResourceData;
    
    protected static string $model = EvaluationCriteria::class;
    protected static string $panel = 'admin';
    public static string $label = 'Evaluation Criteria';

    
    public static function programOptions(): array
    {
        return Program::query()
            ->select(['id', 'title'])
            ->get()
            ->pluck('title', 'id')
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


    public static function index(): \Inertia\Response
    {
        $table = (new TableBuilder(static::$model))
        ->column('program.title', 'Programme')
        ->column('label', 'Label')
        // ->column('description', 'Description')
        ->column('max_score', 'Max Score')
        ->column('weight', 'Weight')
        ->make();

        return Inertia::render(static::getComponentPath('index'), [
            'table' => $table,
            'resource' => static::getResourceData(),
        ]);
    }

    public static function show($id): \Inertia\Response
    {
        $evaluationcriteria = static::$model::with([
            'program',
        ])->findOrFail($id);
    
        return Inertia::render(static::getComponentPath('show'), [
            'evaluationcriteria' => $evaluationcriteria,
            'resource' => static::getResourceData($evaluationcriteria),
        ]);
    }
    
    public static function create(): \Inertia\Response
    {
        $form = (new FormBuilder())
        ->field('program_id', 'select', [
                        'options' => \App\Models\Program::pluck('title', 'id'),
                        'required' => true
                    ])
        ->field('label', 'text', ['required' => true])
        ->field('description', 'textarea', ['required' => true])
        ->field('max_score', 'text', ['required' => true])
        ->field('weight', 'text', ['required' => true])
        ->make();

        return Inertia::render(static::getComponentPath('create'), [
            'form' => $form,
            'resource' => static::getResourceData(),
        ]);
    }

    public static function store(): \Illuminate\Http\RedirectResponse
    {
        $data = request()->validate([
            'program_id' => 'required|exists:programs,id',
            'label' => 'string|required',
            'description' => 'string|required',
            'max_score' => 'string|required',
            'weight' => 'string|required',
            
        ]);

        static::$model::create($data);

        return redirect()->route(static::getRouteName('index'));
    }

    public static function edit($id): \Inertia\Response
    {
        $evaluationcriteria = static::$model::findOrFail($id);

        $form = (new FormBuilder())
        ->field('program_id', 'select', [
                        'options' => \App\Models\Program::pluck('title', 'id'),
                        'value' => $evaluationcriteria->program_id,
                        'required' => true
                    ])
        ->field('label', 'text', [
                        'required' => true,
                        'value' => $evaluationcriteria->label
                    ])
        ->field('description', 'textarea', [
                        'required' => true,
                        'value' => $evaluationcriteria->description
                    ])
        ->field('max_score', 'text', [
                        'required' => true,
                        'value' => $evaluationcriteria->max_score
                    ])
        ->field('weight', 'text', [
                        'required' => true,
                        'value' => $evaluationcriteria->weight
                    ])
        ->make();

        return Inertia::render(static::getComponentPath('edit'), [
            'evaluationcriteria' => $evaluationcriteria,
            'form' => $form,
            'resource' => static::getResourceData($evaluationcriteria),
        ]);
    }

    public static function update($id): \Illuminate\Http\RedirectResponse
    {
        $evaluationcriteria = static::$model::findOrFail($id);
    
        $data = request()->validate([
            'program_id' => 'required|exists:programs,id',
            'label' => 'string|required',
            'description' => 'string|required',
            'max_score' => 'string|required',
            'weight' => 'string|required',
            
        ]);
    
        $evaluationcriteria->update($data);
    
        return redirect()->route(static::getRouteName('index'));
    }

    public static function destroy($id): \Illuminate\Http\RedirectResponse
    {
        $evaluationcriteria = static::$model::findOrFail($id);
        $evaluationcriteria->delete();
        return redirect()->route(static::getRouteName('index'));
    }

    protected static function getComponentPath(string $view): string
    {
        return static::$panel . '/Resources/' . class_basename(static::class) . '/' . $view;
    }
}