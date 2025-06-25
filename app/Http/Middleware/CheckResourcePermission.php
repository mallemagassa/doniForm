<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CheckResourcePermission
{
    public function handle(Request $request, Closure $next)
    {
        $routeName = $request->route()->getName();
        $parts = explode('.', $routeName);
        
        if (count($parts) !== 3) {
            return $next($request);
        }

        [$panel, $resource, $action] = $parts;
        $permissionAction = $this->mapActionToPermission($action);
        $permissionName = "{$permissionAction} {$resource}";

        if (!auth()->user()->can($permissionName)) {
            abort(403);
        }

        return $next($request);
    }

    protected function mapActionToPermission(string $action): string
    {
        $mapping = [
            'index' => 'viewAny',
            'show' => 'view',
            'create' => 'create',
            'store' => 'create',
            'edit' => 'update',
            'update' => 'update',
            'destroy' => 'delete',
        ];

        return $mapping[$action] ?? $action;
    }
}