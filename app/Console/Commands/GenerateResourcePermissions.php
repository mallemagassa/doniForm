<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Str;

class GenerateResourcePermissions extends Command
{
    protected $signature = 'permissions:generate';
    protected $description = 'Generate permissions for all resources';

    public function handle()
    {
        $resources = [
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

        $actions = ['viewAny', 'view', 'create', 'update', 'delete', 'restore', 'forceDelete'];

        foreach ($resources as $resource) {
            $modelName = Str::snake(class_basename($resource));
            
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'name' => "{$action} {$modelName}",
                    'guard_name' => 'web',
                ]);
                
                $this->info("Created permission: {$action} {$modelName}");
            }
        }

        $this->info('All permissions generated successfully!');
    }
}