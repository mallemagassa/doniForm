<?php

namespace App\Resources;

use Inertia\Response;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

abstract class Resource
{
    protected static string $model;
    protected static string $panel;
    protected static string $label;

    public static function route(string $action, array $parameters = []): string
    {
        return route(static::getRouteName($action), $parameters);
    }

    public static function getRouteName(string $action): string
    {
        return static::$panel . '.' . Str::kebab(class_basename(static::class)) . '.' . $action;
    }

    public static function getPermissionName(): string
    {
        return Str::snake(class_basename(static::class));
    }

    public static function getModelClass(): string
    {
        return static::$model;
    }

    public static function getPermissionActions(): array
    {
        return ['viewAny', 'view', 'create', 'update', 'delete', 'restore', 'forceDelete'];
    }

    abstract public static function index(): Response;
    abstract public static function show(Model $model): Response;
    abstract public static function create(): Response;
    abstract public static function store(): \Illuminate\Http\RedirectResponse;
    abstract public static function edit(Model $model): Response;
    abstract public static function update($id): \Illuminate\Http\RedirectResponse;
    abstract public static function destroy(Model $model): \Illuminate\Http\RedirectResponse;
}