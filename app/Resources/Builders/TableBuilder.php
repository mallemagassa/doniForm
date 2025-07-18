<?php

namespace App\Resources\Builders;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

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

    public function make(Request $request): array
    {
        // Log::info('Making table with request:', [
        //     'filters' => $request->input('filters'),
        //     'search' => $request->input('search')
        // ]);

        // dd($request->input('filters'));
    
        $this->applyFilters($request);
        $this->query->with($this->relations);
    
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
    
        $records = $this->query->paginate($perPage, ['*'], 'page', $page);
    
        return [
            'records' => $records,
            'columns' => $this->columns,
            'per_page' => $records->perPage(),
            'current_page' => $records->currentPage(),
            'total' => $records->total(),
            'last_page' => $records->lastPage(),
        ];
    }
    
    protected function applyFilters(Request $request): void
    {
        $filters = $request->input('filters', []);
        Log::info('Applying filters:', $filters);
    
        foreach ($filters as $column => $value) {
            if (empty($value) && $value !== '0') {
                continue;
            }
    
            try {
                // Si le point fait partie du nom de colonne (pas une relation)
                if (str_contains($column, '.') && !$this->isRelationColumn($column)) {
                    // Remplacer les points par des underscores pour le nom de colonne
                    $columnName = str_replace('.', '_', $column);
                    $this->query->where($columnName, 'LIKE', "%{$value}%");
                } 
                // Si c'est une vraie relation
                elseif (str_contains($column, '.')) {
                    $this->applyRelationFilter($column, $value);
                } 
                // Colonne simple
                else {
                    $this->query->where($column, 'LIKE', "%{$value}%");
                }
            } catch (\Exception $e) {
                Log::error("Failed to apply filter on column {$column}: " . $e->getMessage());
                continue;
            }
        }
    }

    protected function applyRelationFilter(string $column, string $value): void
    {
        $relations = explode('.', $column);
        $field = array_pop($relations);
        $relation = implode('.', $relations);

        $this->query->whereHas($relation, function($query) use ($field, $value) {
            $query->where($field, 'LIKE', "%{$value}%");
        });
    }
        
    protected function isRelationColumn(string $column): bool
    {
        $relations = explode('.', $column);
        array_pop($relations); // Enlever le dernier élément (le champ)
        $relationPath = implode('.', $relations);
        
        return in_array($relationPath, $this->relations);
    }

}
