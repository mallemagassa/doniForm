<?php

namespace App\Resources\Builders;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use ReflectionClass;

class TableBuilder
{
    protected Builder $query;
    protected array $columns = [];
    protected string $modelClass;
    protected string $tableName;

    public function __construct(string $modelClass)
    {
        $this->modelClass = $modelClass;
        $this->query = $modelClass::query();
        $this->tableName = (new $modelClass)->getTable();
    }

    public function withCount(array|string $relations): self
    {
        $this->query->withCount($relations);
        return $this;
    }


    public function column(string $name, ?string $label = null): self
    {
        $this->columns[$name] = $label ?? Str::title($name);
        return $this;
    }

    public function make(): array
    {
        foreach ($this->columns as $column => $label) {
            if (Str::contains($column, '.')) {
                $relations = explode('.', $column);
                $this->query->with(implode('.', array_slice($relations, 0, -1)));
            }
        }
    
        $records = $this->query->paginate(100);
    
        // Transformer les données pour aplatir les relations
        $records->getCollection()->transform(function ($item) {
            foreach ($this->columns as $column => $label) {
                if (Str::contains($column, '.')) {
                    $value = data_get($item, $column);
                    $item->{$column} = $value;
                }
            }
            return $item;
        });
    
        return [
            'columns' => $this->columns,
            'records' => $records
        ];
    }
}