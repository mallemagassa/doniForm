<?php

namespace App\Providers;


use Dom\Document;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;
use App\Resources\Admin\RoleResource;
use App\Resources\Admin\UserResource;
use App\Resources\Admin\RegionResource;
use Illuminate\Support\ServiceProvider;
use App\Resources\Admin\ProgramResource;
use App\Resources\Admin\DocumentResource;
use App\Resources\Admin\FormFieldResource;
use App\Resources\Admin\EvaluationResource;
use App\Resources\Admin\ApplicationResource;
use App\Resources\Admin\FormProgramResource;
use App\Resources\Admin\EvaluationCriteriaResource;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->register(PanelServiceProvider::class);
    
        PanelServiceProvider::registerPanels([
            'admin' => [
                'path' => 'admin',
                'middleware' => ['web', 'auth'],
                'layout' => 'AppLayout',
                'resources' => [
                    UserResource::class,
                    ProgramResource::class,
                    ApplicationResource::class,
                    DocumentResource::class,
                    EvaluationResource::class,
                    EvaluationCriteriaResource::class,
                    //FormFieldResource::class,
                    RegionResource::class,
                    FormProgramResource::class,
                    RoleResource::class,
                ]
            ]
        ]);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function ($user, $ability) {
            return $user->hasRole('super-admin') ? true : null;
        });

        Inertia::share([
            'panel' => fn () => PanelServiceProvider::getDefaultPanel(),
            'defaultPanel' => fn () => PanelServiceProvider::getDefaultPanel(),
            'resources' => function () {
                $panel = PanelServiceProvider::getActivePanel();
                $config = PanelServiceProvider::getPanelConfig($panel);
    
                if (! $config || ! isset($config['resources'])) {
                    return [];
                }
    
                return array_map(function ($resource) {
                    return [
                        'name' => class_basename($resource),
                        'routeName' => Str::kebab(class_basename($resource)),
                        'label' => $resource::$label ?? Str::title(Str::snake(class_basename($resource), ' ')),
                    ];
                }, $config['resources']);
            },
        ]);
    }
}
