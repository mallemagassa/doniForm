<?php

namespace App\Resources\Admin;

use App\Models\Region;
use Inertia\Inertia;
use App\Resources\Resource;
use App\Resources\Builders\FormBuilder;
use App\Resources\Builders\TableBuilder;
use App\Resources\Concerns\HasResourceData;
use App\Models\Program;

class RegionResource extends Resource
{
    use HasResourceData;
    
    protected static string $model = Region::class;
    protected static string $panel = 'admin';
    protected static string $label = 'Region';

    
    public static function programsOptions(): array
    {
        return Program::query()
            ->select(['id', 'name'])
            ->get()
            ->pluck('name', 'id')
            ->toArray();
    }


    public static function index(): \Inertia\Response
    {
        $table = (new TableBuilder(static::$model))
        ->column('name', 'Name')
        ->column('description', 'Description')
        ->column('country', 'Country')
        ->column('status', 'Status')
        ->make();

        return Inertia::render(static::getComponentPath('index'), [
            'table' => $table,
            'resource' => static::getResourceData(),
        ]);
    }

    public static function show($id): \Inertia\Response
    {
        $region = static::$model::findOrFail($id);
    
        return Inertia::render(static::getComponentPath('show'), [
            'region' => $region,
            'resource' => static::getResourceData($region),
        ]);
    }
    
    public static function create(): \Inertia\Response
    {
        $form = (new FormBuilder())
        ->field('name', 'text', ['required' => true])
        ->field('description', 'text', ['required' => true])
        ->field('country', 'text', ['required' => true])
        ->field('status', 'select', 
        [
                'options' => [
                    'active' => 'active',
                    'inactive' => 'inactive',
                ],
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
            'name' => 'string|required',
            'description' => 'string|required',
            'country' => 'string|required',
            'status' => 'string|required',
            
        ]);

        static::$model::create($data);

        return redirect()->route(static::getRouteName('index'));
    }

    public static function edit($id): \Inertia\Response
    {
        $region = static::$model::findOrFail($id);

        $form = (new FormBuilder())
        ->field('name', 'text', [
                        'required' => true,
                        'value' => $region->name
                    ])
        ->field('description', 'text', [
                        'required' => true,
                        'value' => $region->description
                    ])
        ->field('country', 'text', [
                        'required' => true,
                        'value' => $region->country
                    ])
        ->field('status', 'select', 
        [
                'value' => $region->status,
                'options' => [
                    'active' => 'active',
                    'inactive' => 'inactive',
                ],
                'required' => true
            ])
        ->make();

        return Inertia::render(static::getComponentPath('edit'), [
            'region' => $region,
            'form' => $form,
            'resource' => static::getResourceData($region),
        ]);
    }

    public static function update($id): \Illuminate\Http\RedirectResponse
    {
        $region = static::$model::findOrFail($id);
    
        $data = request()->validate([
            'name' => 'string|required',
            'description' => 'string|required',
            'country' => 'string|required',
            'status' => 'string|required',
            
        ]);
    
        $region->update($data);
    
        return redirect()->route(static::getRouteName('index'));
    }

    public static function destroy($id): \Illuminate\Http\RedirectResponse
    {
        $region = static::$model::findOrFail($id);
        $region->delete();
        return redirect()->route(static::getRouteName('index'));
    }

    protected static function getComponentPath(string $view): string
    {
        return static::$panel . '/Resources/' . class_basename(static::class) . '/' . $view;
    }
}