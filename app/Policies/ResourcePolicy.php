<?php

// app/Policies/ResourcePolicy.php
namespace App\Policies;

use Illuminate\Support\Str;
use Illuminate\Auth\Access\HandlesAuthorization;

class ResourcePolicy
{
    use HandlesAuthorization;

    protected string $permissionName;

    public function __construct()
    {
        $this->permissionName = Str::snake(class_basename($this));
    }

    public function before($user, $ability)
    {
        if ($user->hasRole('admin')) {
            return true;
        }
    }

    public function viewAny($user)
    {
        return $user->can("viewAny {$this->permissionName}");
    }
    public function view($user, $model)
    {
        return $user->can("view {$this->permissionName}", $model);
    }

    public function create($user)
    {
        return $user->can("create {$this->permissionName}");
    }
    
    public function update($user, $model)
    {
        return $user->can("update {$this->permissionName}", $model);
    }

    public function delete($user, $model)
    {
        return $user->can("delete {$this->permissionName}", $model);
    }

    public function restore($user, $model)
    {
        return $user->can("restore {$this->permissionName}", $model);
    }

    public function forceDelete($user, $model)
    {
        return $user->can("forceDelete {$this->permissionName}", $model);
    }

    
}