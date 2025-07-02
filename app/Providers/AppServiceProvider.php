<?php

namespace App\Providers;

use Dom\Document;
use Inertia\Inertia;
use App\Models\GrilleItem;
use App\Models\GrilleLabel;
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
use App\Resources\Admin\GrilleItemResource;
use App\Resources\Admin\ApplicationResource;
use App\Resources\Admin\FormProgramResource;
use App\Resources\Admin\GrilleLabelResource;
use App\Resources\Admin\EvaluationCriteriaResource;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->register(PanelServiceProvider::class);

        PanelServiceProvider::registerCustomLinks([
            'admin' => [
                [
                    'label' => 'Applications approuvées',
                    'name' => 'ApplicationsApprouves',
                    'routeName' => 'applications.approved',
                    'group' => 'Évaluation',
                    'path' => 'applications/approved',
                    'icon' => 'CheckCircle',
                ],
            ]
        ]);
    
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
                    GrilleLabelResource::class,
                    GrilleItemResource::class
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
            return $user->hasRole('admin') ? true : null;
        });

        Inertia::share([
            'resources' => function () {
                $panel = PanelServiceProvider::getActivePanel();
                $config = PanelServiceProvider::getPanelConfig($panel);
        
                if (! $config || ! isset($config['resources'])) {
                    return [];
                }
        
                $grouped = [];
        
                // Regrouper les resources normales
                foreach ($config['resources'] as $resource) {
                    $group = $resource::$group ?? null;
        
                    $item = [
                        'name' => class_basename($resource),
                        'routeName' => Str::kebab(class_basename($resource)),
                        'label' => $resource::$label ?? Str::title(Str::snake(class_basename($resource), ' ')),
                    ];
        
                    if ($group) {
                        $grouped[$group][] = $item;
                    } else {
                        $grouped[] = $item;
                    }
                }
        
                // Ajouter les liens personnalisés
                $customLinks = PanelServiceProvider::getCustomLinks($panel);
        
                foreach ($customLinks as $link) {
                    $grouped[$link['group']][] = [
                        'name' => Str::slug($link['label']),
                        'routeName' => $link['path'],
                        'label' => $link['label'],
                        'icon' => $link['icon'] ?? 'Folder',
                        'is_custom' => true,
                    ];
                }
        
                return $grouped;
            },
        ]);
        
        
        
    }
}
