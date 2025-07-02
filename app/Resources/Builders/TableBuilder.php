<?php

namespace App\Resources\Builders;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TableBuilder
{
    protected Builder $query;
    protected array $columns = [];
    protected string $modelClass;
    protected array $callbacks = [];
    protected array $relations = [];

    public function __construct(string $modelClass)
    {
        $this->modelClass = $modelClass;
        $this->query = $modelClass::query();
    }

    public function query(\Closure $callback): self
    {
        $callback($this->query);
        return $this;
    }

    public function with(array $relations): self
    {
        $this->relations = array_merge($this->relations, $relations);
        $this->query->with($relations);
        return $this;
    }

    public function withCount($relations): self
    {
        $this->query->withCount($relations);
        return $this;
    }

    public function column(string $name, ?string $label = null): self
    {
        $this->columns[$name] = $label ?? Str::title($name);

        if (Str::contains($name, '.')) {
            $relationPath = implode('.', array_slice(explode('.', $name), 0, -1));
            if (!in_array($relationPath, $this->relations)) {
                $this->relations[] = $relationPath;
                $this->query->with($relationPath);
            }
        }

        return $this;
    }

    public function columnCallback(string $key, string $label, \Closure $callback): self
    {
        $this->columns[$key] = $label;
        $this->callbacks[$key] = $callback;
        return $this;
    }

    public function make(): array
    {
        $this->query->with($this->relations);

        $records = $this->query->paginate(100);

        $records->getCollection()->transform(function ($item) {
            foreach ($this->columns as $column => $label) {
                if (Str::contains($column, '.')) {
                    $item->{$column} = data_get($item, $column);
                }
            }

            foreach ($this->callbacks as $key => $callback) {
                $item->{$key} = $callback($item);
            }

            return $item;
        });

        return [
            'columns' => $this->columns,
            'records' => $records
        ];
    }
}
