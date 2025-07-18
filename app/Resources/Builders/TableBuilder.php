<?php

namespace App\Resources\Builders;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class TableBuilder
{
    protected Builder $query;
    protected array $columns = [];
    protected string $modelClass;
    protected array $callbacks = [];
    protected array $relations = [];
    protected array $orders = [];
    protected array $filterableColumns = [];

    public function __construct(string $modelClass)
    {
        $this->modelClass = $modelClass;
        $this->query = $modelClass::query()->latest();
    }

    public function query(\Closure $callback): self
    {
        $callback($this->query);
        return $this;
    }

    public function orderBy(string $column, string $direction = 'asc'): self
    {
        $this->orders[] = [
            'column' => $column,
            'direction' => $direction,
        ];
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

    public function filterableColumn(string $column, \Closure $filterCallback = null): self
    {
        $this->filterableColumns[$column] = $filterCallback ?? function ($query, $value) use ($column) {
            if (Str::contains($column, '.')) {
                $relation = Str::beforeLast($column, '.');
                $columnName = Str::afterLast($column, '.');
                $query->whereHas($relation, fn ($q) => $q->where($columnName, 'like', "%{$value}%"));
            } else {
                $query->where($column, 'like', "%{$value}%");
            }
        };
        return $this;
    }

    public function make(Request $request): array
    {
        $this->query->with($this->relations);
    
        // Filtre global
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $this->query->where(function($q) use ($searchTerm) {
                foreach ($this->filterableColumns as $column => $callback) {
                    $callback($q, $searchTerm);
                }
            });
        }
        
        // Filtres par colonne
        foreach ($this->filterableColumns as $column => $callback) {
            if ($request->filled("filters.{$column}")) {
                $callback($this->query, $request->input("filters.{$column}"));
            }
        }
    
        // Gestion du tri
        foreach ($this->orders as $order) {
            $this->query->orderBy($order['column'], $order['direction']);
        }
    
        $records = $this->query->paginate($request['per_page'] ?? 5);
    
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
            'filterable_columns' => array_keys($this->filterableColumns),
            'records' => [
                'data' => $records->items(),
                'meta' => [
                    'current_page' => $records->currentPage(),
                    'last_page' => $records->lastPage(),
                    'per_page' => $records->perPage(),
                    'total' => $records->total(),
                    'from' => $records->firstItem(),
                    'to' => $records->lastItem(),
                ],
                'applied_filters' => [
                    'search' => $request->input('search', ''),
                    'column_filters' => $request->input('filters', [])
                ]
            ]
        ];
    }
}
