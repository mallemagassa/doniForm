<?php

namespace App\Providers;

use Illuminate\Support\Str;
use App\Policies\ResourcePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission;

class PermissionServiceProvider extends ServiceProvider
{
    protected $resources = [
        \App\Resources\Admin\UserResource::class,
        \App\Resources\Admin\ProgramResource::class,
        \App\Resources\Admin\ApplicationResource::class,
        \App\Resources\Admin\DocumentResource::class,
        \App\Resources\Admin\EvaluationResource::class,
        \App\Resources\Admin\EvaluationCriteriaResource::class,
        \App\Resources\Admin\RegionResource::class,
        \App\Resources\Admin\FormProgramResource::class,
        \App\Resources\Admin\RoleResource::class,
    ];

    public function boot()
    {
        $this->registerPolicies();
    }
    
    protected function registerPolicies()
    {
        foreach ($this->resources as $resource) {
            $modelClass = $resource::getModelClass();
            Gate::policy($modelClass, ResourcePolicy::class);
        }
    }
    
}