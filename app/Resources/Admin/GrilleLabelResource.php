<?php

namespace App\Resources\Admin;

use Inertia\Inertia;
use App\Models\Program;
use App\Models\GrilleItem;
use App\Models\GrilleLabel;
use App\Resources\Resource;
use Illuminate\Http\Request;
use App\Resources\Builders\FormBuilder;
use App\Resources\Builders\TableBuilder;
use App\Resources\Concerns\HasResourceData;

class GrilleLabelResource extends Resource
{
    use HasResourceData;
    
    protected static string $model = GrilleLabel::class;
    protected static string $panel = 'admin';
    protected static string $label = 'Grille Label';
    public static string $group = 'Évaluation';

    
    public static function grilleItemsOptions(): array
    {
        return GrilleItem::query()
            ->select(['id', 'titre'])
            ->get()
            ->pluck('titre', 'id')
            ->toArray();
    }

    public static function programOptions(): array
    {
        return Program::query()
            ->select(['id', 'title'])
            ->get()
            ->pluck('title', 'id')
            ->toArray();
    }


    public static function index(Request $request): \Inertia\Response
    {
        $table = (new TableBuilder(static::$model))
        ->column('nom', 'Nom')
        ->column('program.title', 'Programme')
        ->make($request);

        return Inertia::render(static::getComponentPath('index'), [
            'table' => $table,
            'resource' => static::getResourceData(),
        ]);
    }

    public static function show($id): \Inertia\Response
    {
        $application = static::$model::with(array_keys((new static::$model)->getRelations()))
            ->findOrFail($id);
    
        return Inertia::render(static::getComponentPath('show'), [
            'application' => $application->loadMissing(array_keys($application->getRelations())),
            'resource' => static::getResourceData($application),
        ]);
    }
    
    public static function create(): \Inertia\Response
    {
        $form = (new FormBuilder())
        ->field('nom', 'text', ['required' => true])
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

    public static function store(): \Illuminate\Http\RedirectResponse
    {
        $data = request()->validate([
            'nom' => 'string|required',
            'program_id' => 'required|exists:programs,id',
        ]);

        static::$model::create($data);

        return redirect()->route(static::getRouteName('index'));
    }

    public static function edit($id): \Inertia\Response
    {
        $grillelabel = static::$model::findOrFail($id);

        $form = (new FormBuilder())
        ->field('nom', 'text', [
                        'required' => true,
                        'value' => $grillelabel->nom
                    ])
        ->field('program_id', 'select', [
                        'options' => Program::pluck('title', 'id'),
                        'value' => $grillelabel->program_id,
                        'required' => true
                    ])
        ->make();

        return Inertia::render(static::getComponentPath('edit'), [
            'grillelabel' => $grillelabel,
            'form' => $form,
            'resource' => static::getResourceData($grillelabel),
        ]);
    }

    public static function update($id): \Illuminate\Http\RedirectResponse
    {
        $grillelabel = static::$model::findOrFail($id);
    
        $data = request()->validate([
            'nom' => 'string|required',
            'program_id' => 'required|exists:programs,id',
            
        ]);
    
        $grillelabel->update($data);
    
        return redirect()->route(static::getRouteName('index'));
    }

    public static function destroy($id): \Illuminate\Http\RedirectResponse
    {
        $grillelabel = static::$model::findOrFail($id);
        $grillelabel->delete();
        return redirect()->route(static::getRouteName('index'));
    }

    protected static function getComponentPath(string $view): string
    {
        return static::$panel . '/Resources/' . class_basename(static::class) . '/' . $view;
    }
}