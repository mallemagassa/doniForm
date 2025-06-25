<?php
namespace App\Resources\Concerns;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

trait HasResourceData
{
    public static function getResourceData(?Model $model = null): array
    {
        $routes = [
            'index' => static::route('index'),
            'create' => static::route('create'),
            'store' => static::route('store'),
    
            'edit' => static::route('edit', ['id' => ':id']),
            'update' => static::route('update', ['id' => ':id']),
            'destroy' => static::route('destroy', ['id' => ':id']),
            'show' => static::route('show', ['id' => ':id']),
        ];
    
        if ($model && $model->exists) {
            $id = $model->getKey();
    
            $routes['edit'] = static::route('edit', ['id' => $id]);
            $routes['update'] = static::route('update', ['id' => $id]);
            $routes['destroy'] = static::route('destroy', ['id' => $id]);
            $routes['show'] = static::route('show', ['id' => $id]);
        }
    
        return [
            'name' => Str::kebab(class_basename(static::class)),
            'label' => static::$label ?? Str::headline(class_basename(static::class)),
            'routes' => $routes,
        ];
    }
    
}


