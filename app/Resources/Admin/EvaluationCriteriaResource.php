<?php

namespace App\Resources\Admin;

use Inertia\Inertia;
use App\Models\Program;
use App\Models\Evaluation;
use App\Resources\Resource;
use Illuminate\Http\Request;
use App\Models\EvaluationCriteria;
use App\Resources\Builders\FormBuilder;
use App\Resources\Builders\TableBuilder;
use App\Resources\Concerns\HasResourceData;

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


    public static function index(Request $request): \Inertia\Response
    {
        $table = (new TableBuilder(static::$model))
        ->column('program.title', 'Programme')
        ->column('label', 'Label')
        ->make($request);

        return Inertia::render(static::getComponentPath('index'), [
            'table' => $table,
            'resource' => static::getResourceData(),
        ]);
    }

    public static function show($id): \Inertia\Response
    {
        $evaluationcriteria = static::$model::with([
            'program',
            'evaluationCriteriaItems'
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
                'options' => Program::pluck('title', 'id'),
                'required' => true
            ])
            ->field('label', 'text', ['required' => true])
            ->field('description', 'textarea', ['required' => true])
            ->field('items', 'repeater', [
                'fields' => [
                    'title' => ['type' => 'text', 'required' => true],
                    'description' => ['type' => 'textarea', 'required' => true]
                ]
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
            'program_id' => 'required|exists:programs,id',
            'label' => 'string|required',
            'description' => 'string|required',
            'items' => 'required|array',
            'items.*.title' => 'required|string',
            'items.*.description' => 'required|string',
        ]);

        $evaluationCriteria = static::$model::create($data);
        
        // Sauvegarder les items
        foreach ($data['items'] as $item) {
            $evaluationCriteria->evaluationCriteriaItems()->create($item);
        }

        return redirect()->route(static::getRouteName('index'));
    }
        
    // public static function create(): \Inertia\Response
    // {
    //     $form = (new FormBuilder())
    //     ->field('program_id', 'select', [
    //                     'options' => Program::pluck('title', 'id'),
    //                     'required' => true
    //                 ])
    //     ->field('label', 'text', ['required' => true])
    //     ->field('description', 'textarea', ['required' => true])
    //     // ->field('max_score', 'text', ['required' => true])
    //     // ->field('weight', 'text', ['required' => true])
    //     ->make();

    //     return Inertia::render(static::getComponentPath('create'), [
    //         'form' => $form,
    //         'resource' => static::getResourceData(),
    //     ]);
    // }

    // public static function store(): \Illuminate\Http\RedirectResponse
    // {
    //     $data = request()->validate([
    //         'program_id' => 'required|exists:programs,id',
    //         'label' => 'string|required',
    //         'description' => 'string|required',
    //         'max_score' => 'string|required',
    //         'weight' => 'string|required',
            
    //     ]);

    //     static::$model::create($data);

    //     return redirect()->route(static::getRouteName('index'));
    // }

    // public static function edit($id): \Inertia\Response
    // {
    //     $evaluationcriteria = static::$model::findOrFail($id);

    //     $form = (new FormBuilder())
    //     ->field('program_id', 'select', [
    //                     'options' => Program::pluck('title', 'id'),
    //                     'value' => $evaluationcriteria->program_id,
    //                     'required' => true
    //                 ])
    //     ->field('label', 'text', [
    //                     'required' => true,
    //                     'value' => $evaluationcriteria->label
    //                 ])
    //     ->field('description', 'textarea', [
    //                     'required' => true,
    //                     'value' => $evaluationcriteria->description
    //                 ])
    //     // ->field('max_score', 'text', [
    //     //                 'required' => true,
    //     //                 'value' => $evaluationcriteria->max_score
    //     //             ])
    //     // ->field('weight', 'text', [
    //     //                 'required' => true,
    //     //                 'value' => $evaluationcriteria->weight
    //     //             ])
    //     ->make();

    //     return Inertia::render(static::getComponentPath('edit'), [
    //         'evaluationcriteria' => $evaluationcriteria,
    //         'form' => $form,
    //         'resource' => static::getResourceData($evaluationcriteria),
    //     ]);
    // }

    // public static function update($id): \Illuminate\Http\RedirectResponse
    // {
    //     $evaluationcriteria = static::$model::findOrFail($id);
    
    //     $data = request()->validate([
    //         'program_id' => 'required|exists:programs,id',
    //         'label' => 'string|required',
    //         'description' => 'string|required',
    //         'max_score' => 'string|required',
    //         'weight' => 'string|required',
            
    //     ]);
    
    //     $evaluationcriteria->update($data);
    
    //     return redirect()->route(static::getRouteName('index'));
    // }

    public static function edit($id): \Inertia\Response
    {
        $evaluationcriteria = static::$model::with('evaluationCriteriaItems')->findOrFail($id);

        $form = (new FormBuilder())
            ->field('program_id', 'select', [
                'options' => Program::pluck('title', 'id'),
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
            ->field('items', 'repeater', [
                'fields' => [
                    'title' => ['type' => 'text', 'required' => true],
                    'description' => ['type' => 'textarea', 'required' => true]
                ],
                'value' => $evaluationcriteria->evaluationCriteriaItems->map(function ($item) {
                    return [
                        'id' => $item->id, // Important pour les updates
                        'title' => $item->title,
                        'description' => $item->description
                    ];
                })->toArray()
            ])
            ->make();

        return Inertia::render(static::getComponentPath('edit'), [
            'evaluationCriteria' => $evaluationcriteria,
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
            'items' => 'required|array',
            'items.*.title' => 'required|string',
            'items.*.description' => 'required|string',
        ]);
        
        // Mise à jour du critère principal
        $evaluationcriteria->update($data);
        
        // Gestion des items
        $existingIds = collect($data['items'])->pluck('id')->filter()->toArray();
        
        // Supprimer les items qui ne sont plus présents
        $evaluationcriteria->evaluationCriteriaItems()
            ->whereNotIn('id', $existingIds)
            ->delete();
        
        // Mettre à jour ou créer les items
        foreach ($data['items'] as $item) {
            if (isset($item['id'])) {
                $evaluationcriteria->evaluationCriteriaItems()
                    ->where('id', $item['id'])
                    ->update($item);
            } else {
                $evaluationcriteria->evaluationCriteriaItems()->create($item);
            }
        }
        
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