<?php

namespace App\Resources\Admin;

use Inertia\Inertia;
use Inertia\Response;
use App\Resources\Resource;
use Spatie\Permission\Models\Role;
use App\Resources\Builders\FormBuilder;
use App\Resources\Builders\TableBuilder;
use Spatie\Permission\Models\Permission;
use App\Resources\Concerns\HasResourceData;

class RoleResource extends Resource
{

    use HasResourceData;
    protected static string $model = Role::class;
    protected static string $panel = 'admin';
    public static string $label = 'Rôles';

    // public static string $group = 'Utilisateurs';
    public static string $group = 'Parametre';
    public static function index(): Response
    {
        $table = (new TableBuilder(static::$model))
        ->withCount('permissions') // 👈 ajout du count ici
        ->column('name', 'Nom')
        ->column('guard_name', 'Nom du garde')
        ->column('permissions_count', 'Nombre de permissions')
        ->make();
    
        return Inertia::render(static::getComponentPath('index'), [
            'table' => $table,
            'resource' => static::getResourceData(),
        ]);
    }
    

    public static function show($id): Response
    {
        $role = static::$model::findOrFail($id);

        return Inertia::render(static::getComponentPath('show'), [
            'role' => $role->load('permissions'),
            'permissions' => Permission::all()->groupBy('group'),
            'resource' => static::getResourceData(),
        ]);
    }

    public static function create(): Response
    {
        $form = (new FormBuilder())
            ->field('name', 'text', ['required' => true])
            ->field('guard_name', 'text', ['required' => true, 'default' => 'web'])
            ->field('permissions', 'permission-select', [
                'options' => Permission::all()->groupBy('group'),
                'multiple' => true
            ])
            ->make();
    
        return Inertia::render(static::getComponentPath('create'), [
            'form' => $form,
            'resource' => static::getResourceData(),
        ]);
    }

    public static function store(): \Illuminate\Http\RedirectResponse
    {
        $validated = request()->validate([
            'name' => 'required|string|unique:roles',
            'guard_name' => 'required|string',
            'permissions' => 'array',
        ]);

        $role = static::$model::create([
            'name' => $validated['name'],
            'guard_name' => $validated['guard_name']
        ]);
        $role->syncPermissions($validated['permissions'] ?? []);

        return redirect()->route(static::getRouteName('index'));
    }

    public static function edit($id): Response
    {
        $role = static::$model::findOrFail($id);
        
        $form = (new FormBuilder())
            ->field('name', 'text', ['required' => true, 'default' => $role->name])
            ->field('guard_name', 'text', ['required' => true, 'default' => $role->guard_name])
            ->field('permissions', 'permission-select', [
                'options' => Permission::all()->groupBy('group'),
                'multiple' => true,
                'default' => $role->permissions->pluck('id')->toArray()
            ])
            ->make();
    
        return Inertia::render(static::getComponentPath('edit'), [
            'role' => $role,
            'form' => $form,
            'resource' => static::getResourceData(),
        ]);
    }

    public static function update($id): \Illuminate\Http\RedirectResponse
    {
        $role = static::$model::findOrFail($id);

        $validated = request()->validate([
            'name' => 'required|string|unique:roles,name,'.$role->id,
            'guard_name' => 'required|string',
            'permissions' => 'array',
        ]);

        $role->update([
            'name' => $validated['name'],
            'guard_name' => $validated['guard_name']
        ]);

        $role->syncPermissions($validated['permissions'] ?? []);

        return redirect()->route(static::getRouteName('index'));
    }

    public static function destroy($id): \Illuminate\Http\RedirectResponse
    {
        $role = static::$model::findOrFail($id);
        $role->delete();
        return redirect()->route(static::getRouteName('index'));
    }

    protected static function getComponentPath(string $view): string
    {
        return static::$panel . '/Resources/' . class_basename(static::class) . '/' . $view;
    }
}