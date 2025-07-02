<?php

namespace App\Resources\Admin;

use App\Models\GrilleItem;
use Inertia\Inertia;
use App\Resources\Resource;
use App\Resources\Builders\FormBuilder;
use App\Resources\Builders\TableBuilder;
use App\Resources\Concerns\HasResourceData;
use App\Models\GrilleLabel;

class GrilleItemResource extends Resource
{
    use HasResourceData;
    
    protected static string $model = GrilleItem::class;
    protected static string $panel = 'admin';
    protected static string $label = 'Grille Item';
    public static string $group = 'Évaluation';

    
    public static function grilleLabelOptions(): array
    {
        return GrilleLabel::query()
            ->select(['id', 'name'])
            ->get()
            ->pluck('name', 'id')
            ->toArray();
    }


    public static function index(): \Inertia\Response
    {
        $table = (new TableBuilder(static::$model))
        ->column('titre', 'Titre')
        ->column('note_1', 'Note 1')
        ->column('note_2', 'Note 2')
        ->column('note_3', 'Note 3')
        ->column('grille_label_id', 'Grille Label Id')
        ->make();

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
        ->field('titre', 'text', ['required' => true])
        ->field('note_1', 'text', ['required' => true])
        ->field('note_2', 'text', ['required' => true])
        ->field('note_3', 'text', ['required' => true])
        ->field('grille_label_id', 'select', [
            'options' => GrilleLabel::pluck('nom', 'id'),
            'required' => true
        ])
        // ->field('grille_label_id', 'text', ['required' => true])
        ->make();

        return Inertia::render(static::getComponentPath('create'), [
            'form' => $form,
            'resource' => static::getResourceData(),
        ]);
    }

    public static function store(): \Illuminate\Http\RedirectResponse
    {
        $data = request()->validate([
            'titre' => 'string|required',
            'note_1' => 'integer|max:5|required',
            'note_2' => 'integer|max:5|required',
            'note_3' => 'integer|max:5|required',
            'grille_label_id' => 'string|required',
            
        ]);

        static::$model::create($data);

        return redirect()->route(static::getRouteName('index'));
    }

    public static function edit($id): \Inertia\Response
    {
        $grilleitem = static::$model::findOrFail($id);

        $form = (new FormBuilder())
        ->field('titre', 'text', [
                        'required' => true,
                        'value' => $grilleitem->titre
                    ])
        ->field('note_1', 'text', [
                        'required' => true,
                        'value' => $grilleitem->note_1
                    ])
        ->field('note_2', 'text', [
                        'required' => true,
                        'value' => $grilleitem->note_2
                    ])
        ->field('note_3', 'text', [
                        'required' => true,
                        'value' => $grilleitem->note_3
                    ])
        ->field('grille_label_id', 'select', [
                        'options' => GrilleLabel::pluck('nom', 'id'),
                        'value' => $grilleitem->grille_label_id,
                        'required' => true
                    ])
        ->make();

        return Inertia::render(static::getComponentPath('edit'), [
            'grilleitem' => $grilleitem,
            'form' => $form,
            'resource' => static::getResourceData($grilleitem),
        ]);
    }

    public static function update($id): \Illuminate\Http\RedirectResponse
    {
        $grilleitem = static::$model::findOrFail($id);
    
        $data = request()->validate([
            'titre' => 'string|required',
            'note_1' => 'integer|max:5|required',
            'note_2' => 'integer|max:5|required',
            'note_3' => 'integer|max:5|required',
            'grille_label_id' => 'string|required',
            
        ]);
    
        $grilleitem->update($data);
    
        return redirect()->route(static::getRouteName('index'));
    }

    public static function destroy($id): \Illuminate\Http\RedirectResponse
    {
        $grilleitem = static::$model::findOrFail($id);
        $grilleitem->delete();
        return redirect()->route(static::getRouteName('index'));
    }

    protected static function getComponentPath(string $view): string
    {
        return static::$panel . '/Resources/' . class_basename(static::class) . '/' . $view;
    }
}