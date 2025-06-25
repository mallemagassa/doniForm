<?php

namespace App\Resources\Admin;

use App\Models\User;
use Inertia\Inertia;
use App\Resources\Resource;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use App\Resources\Builders\FormBuilder;
use App\Resources\Builders\TableBuilder;
use Spatie\Permission\Models\Permission;
use App\Resources\Concerns\HasResourceData;

class UserResource extends Resource
{
    use HasResourceData;
    
    protected static string $model = User::class;
    protected static string $panel = 'admin';
    public static string $label = 'Utilisateurs';

    
    public static function rolesOptions(): array
    {
        return Role::query()
            ->select(['id', 'name'])
            ->get()
            ->pluck('name', 'id')
            ->toArray();
    }

    public static function permissionsOptions(): array
    {
        return Permission::query()
            ->select(['id', 'name'])
            ->get()
            ->pluck('name', 'id')
            ->toArray();
    }


    public static function index(): \Inertia\Response
    {
        // Gate::authorize('viewAny user_resource', static::$model);
        
        $table = (new TableBuilder(static::$model))
        ->column('name', 'Name')
        ->column('email', 'Email')
        // ->column('email_verified_at', 'Email Verified At')
        // ->column('password', 'Password')
        // ->column('remember_token', 'Remember Token')
        ->make();

        //viewAny user_resource

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
        ->field('name', 'text', ['required' => true])
        ->field('email', 'text', ['required' => true])
        ->field('role', 'select', [
            'options' => Role::pluck('name', 'id'),
            'required' => true
        ])
        ->field('password', 'password', ['required' => true])
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
            'email' => 'email|required|unique:users,email',
            'password' => 'string|required|min:8',
            'role' => 'required|exists:roles,id' 
        ]);
    
        $user = static::$model::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password'])
        ]);
    
        $user->roles()->sync([$data['role']]);
    
        return redirect()->route(static::getRouteName('index'))
            ->with('success', 'Utilisateur créé avec succès');
    }

    public static function edit($id): \Inertia\Response
    {
        $user = static::$model::with('roles')->findOrFail($id);
    
        $form = (new FormBuilder())
            ->field('name', 'text', ['required' => true, 'value' => $user->name])
            ->field('email', 'text', ['required' => true, 'value' => $user->email])
            ->field('role', 'select', [
                'options' => Role::pluck('name', 'id'),
                'value' => $user->roles->first()?->id, // Utilisation de l'opérateur null-safe
                'required' => true
            ])
            ->make();
    
        return Inertia::render(static::getComponentPath('edit'), [
            'user' => $user,
            'form' => $form,
            'resource' => static::getResourceData($user),
        ]);
    }

    public static function update($id): \Illuminate\Http\RedirectResponse
    {
        $data = request()->validate([
            'name' => 'string|required',
            'email' => 'string|required',
            'role' => 'required|exists:roles,id' 
        ]);

        $user = static::$model::findOrFail($id);
        
        $user->update($data);

        $user->roles()->sync([$data['role']]);
    
        return redirect()->route(static::getRouteName('index'));
    }

    public static function destroy($id): \Illuminate\Http\RedirectResponse
    {
        $user = static::$model::findOrFail($id);
        $user->delete();
        return redirect()->route(static::getRouteName('index'));
    }

    protected static function getComponentPath(string $view): string
    {
        return static::$panel . '/Resources/' . class_basename(static::class) . '/' . $view;
    }
}