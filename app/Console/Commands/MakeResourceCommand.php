<?php

namespace App\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Schema;
use ReflectionClass;
use ReflectionMethod;

class MakeResourceCommand extends Command
{
    protected $signature = 'doucsoft:resource {name : The name of the resource} 
                          {--panel=admin : The panel name (admin, manager, etc.)}';

    protected $description = 'Create a new resource with all required files';

    protected $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        parent::__construct();
        $this->filesystem = $filesystem;
    }

    public function handle()
    {
        $name = $this->argument('name');
        $panel = Str::studly($this->option('panel'));
        $resourceName = Str::studly($name);
        $modelName = $resourceName;
        $modelVar = Str::lower($modelName);

        $this->ensureDirectoriesExist($panel, $resourceName);
        $this->createResourceClass($resourceName, $panel, $modelName, $modelVar);
        $this->createVueComponents($resourceName, $panel, $modelName);
        $this->updatePanelConfig($resourceName, $panel);

        $this->info("Resource {$resourceName} created successfully in {$panel} panel!");
    }

    protected function ensureDirectoriesExist(string $panel, string $resourceName): void
    {
        $panelLower = Str::lower($panel);
        $paths = [
            app_path("Resources/{$panel}"),
            resource_path("js/pages/{$panelLower}/Resources/{$resourceName}Resource"),
        ];
    
        foreach ($paths as $path) {
            if (!$this->filesystem->isDirectory($path)) {
                $this->filesystem->makeDirectory($path, 0755, true);
            }
        }
    }
    
    protected function createResourceClass($resourceName, $panel, $modelName, $modelVar)
    {
        $stubPath = __DIR__.'/stubs/resource.stub';
        if (!$this->filesystem->exists($stubPath)) {
            $this->error("Resource stub not found: {$stubPath}");
            return;
        }

        $model = app("App\\Models\\{$modelName}");
        $table = $model->getTable();
        $columns = Schema::getColumnListing($table);
        $fillable = $model->getFillable();
        $columns = array_diff($columns, ['id', 'created_at', 'updated_at', 'deleted_at']);
        
        // Détection des relations
        $relations = $this->detectModelRelations($modelName);
        
        $replacements = [
            '{{resourceName}}' => $resourceName,
            '{{panel}}' => Str::lower($panel),
            '{{panelN}}' => $panel,
            '{{modelName}}' => $modelName,
            '{{modelVar}}' => $modelVar,
            '{{label}}' => Str::headline($resourceName),
            '{{tableColumns}}' => $this->generateTableColumns($columns, $relations),
            '{{validationRules}}' => $this->generateValidationRules($columns, $fillable, $table, $relations),
            '{{updateValidationRules}}' => $this->generateUpdateValidationRules($columns, $fillable, $table, $modelName, $relations),
            '{{formFields}}' => $this->generateFormFields($columns, $fillable, $table, $relations),
            '{{formFieldsWithValues}}' => $this->generateFormFieldsWithValues($columns, $fillable, $table, $modelVar, $relations),
            '{{relations}}' => $this->generateRelationsCode($relations),
            '{{relationImports}}' => $this->generateRelationImports($relations),
        ];

        $stub = $this->filesystem->get($stubPath);
        $stub = str_replace(array_keys($replacements), array_values($replacements), $stub);

        $this->filesystem->put(
            app_path("Resources/{$panel}/{$resourceName}Resource.php"),
            $stub
        );
    }

    protected function detectModelRelations(string $modelName): array
    {
        $model = app("App\\Models\\{$modelName}");
        $relations = [];
        
        try {
            $reflection = new ReflectionClass($model);
            $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
            
            foreach ($methods as $method) {
                // Skip magic methods and special methods
                if (str_starts_with($method->getName(), '__')) {
                    continue;
                }
                
                // Skip methods that don't return a Relation type
                $returnType = $method->getReturnType();
                if (!$returnType) continue;
                
                $returnTypeName = $returnType->getName();
                if (is_subclass_of($returnTypeName, 'Illuminate\\Database\\Eloquent\\Relations\\Relation')) {
                    $relationName = $method->getName();
                    $relationType = class_basename($returnTypeName);
                    
                    // Include all standard Eloquent relation types
                    if (in_array($relationType, ['BelongsTo', 'HasOne', 'HasMany', 'BelongsToMany', 'MorphTo', 'MorphMany'])) {
                        try {
                            $relations[$relationName] = [
                                'type' => $relationType,
                                'related' => $this->getRelatedModelFromRelation($model, $relationName),
                            ];
                        } catch (\Exception $e) {
                            $this->warn("Failed to process relation {$relationName}: " . $e->getMessage());
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->error("Error detecting relations for model {$modelName}: " . $e->getMessage());
        }
        
        $this->info("Detected relations for {$modelName}: " . json_encode($relations, JSON_PRETTY_PRINT));
        
        return $relations;
    }
    protected function getRelatedModelFromRelation($model, string $relationName): string
    {
        $relation = $model->$relationName();
        return get_class($relation->getRelated());
    }

    protected function generateRelationsCode(array $relations): string
    {
        if (empty($relations)) return '';
        
        $code = '';
        foreach ($relations as $relationName => $relation) {
            $relatedModelName = class_basename($relation['related']);
            
            $code .= <<<PHP

    public static function {$relationName}Options(): array
    {
        return {$relatedModelName}::query()
            ->select(['id', 'name'])
            ->get()
            ->pluck('name', 'id')
            ->toArray();
    }

PHP;
        }
        return $code;
    }

    protected function generateRelationImports(array $relations): string
    {
        if (empty($relations)) return '';
        
        $imports = [];
        foreach ($relations as $relation) {
            $imports[] = 'use ' . $relation['related'] . ';';
        }
        return implode("\n", array_unique($imports));
    }

    protected function generateTableColumns(array $columns, array $relations): string
    {
        $result = '';
        foreach ($columns as $column) {
            $relationName = $this->getRelationForColumn($column, $relations);
            
            if ($relationName) {
                $result .= "->column('{$relationName}.name', '".Str::headline($relationName)."')\n        ";
            } else {
                $result .= "->column('{$column}', '".Str::headline($column)."')\n        ";
            }
        }
        return $result;
    }

    protected function generateValidationRules(array $columns, array $fillable, string $table, array $relations): string
    {
        $rules = '';
        foreach ($columns as $column) {
            if (in_array($column, $fillable)) {
                $relationName = $this->getRelationForColumn($column, $relations);
                
                if ($relationName) {
                    $rules .= "'{$column}' => 'required|exists:".$relations[$relationName]['related']::make()->getTable().",id',\n            ";
                } else {
                    $type = Schema::getColumnType($table, $column);
                    $rules .= "'{$column}' => '".$this->getValidationRules($type, $column)."',\n            ";
                }
            }
        }
        return $rules;
    }

    protected function generateUpdateValidationRules(array $columns, array $fillable, string $table, string $modelName, array $relations): string
    {
        $rules = '';
        foreach ($columns as $column) {
            if (in_array($column, $fillable)) {
                $relationName = $this->getRelationForColumn($column, $relations);
                
                if ($relationName) {
                    $rules .= "'{$column}' => 'required|exists:".$relations[$relationName]['related']::make()->getTable().",id',\n            ";
                } else {
                    $type = Schema::getColumnType($table, $column);
                    $rules .= "'{$column}' => '".$this->getUpdateValidationRules($type, $column, $modelName)."',\n            ";
                }
            }
        }
        return $rules;
    }

    protected function generateFormFields(array $columns, array $fillable, string $table, array $relations): string
    {
        $fields = '';
        foreach ($columns as $column) {
            if (in_array($column, $fillable)) {
                $relationName = $this->getRelationForColumn($column, $relations);
                
                if ($relationName) {
                    $relatedModel = $relations[$relationName]['related'];
                    $relatedModelName = class_basename($relatedModel);
                    $fields .= "->field('{$column}', 'select', [
                        'options' => \\{$relatedModel}::pluck('name', 'id'),
                        'required' => true
                    ])\n        ";
                } else {
                    $type = $this->getFormFieldType(Schema::getColumnType($table, $column));
                    $fields .= "->field('{$column}', '{$type}', ['required' => ".($type !== 'boolean' ? 'true' : 'false')."])\n        ";
                }
            }
        }
        return $fields;
    }

    protected function generateFormFieldsWithValues(array $columns, array $fillable, string $table, string $modelVar, array $relations): string
    {
        $fields = '';
        foreach ($columns as $column) {
            if (in_array($column, $fillable)) {
                $relationName = $this->getRelationForColumn($column, $relations);
                
                if ($relationName) {
                    $relatedModel = $relations[$relationName]['related'];
                    $relatedModelName = class_basename($relatedModel);
                    $fields .= "->field('{$column}', 'select', [
                        'options' => \\{$relatedModel}::pluck('name', 'id'),
                        'value' => \${$modelVar}->{$column},
                        'required' => true
                    ])\n        ";
                } else {
                    $type = $this->getFormFieldType(Schema::getColumnType($table, $column));
                    $fields .= "->field('{$column}', '{$type}', [
                        'required' => ".($type !== 'boolean' ? 'true' : 'false').",
                        'value' => \${$modelVar}->{$column}
                    ])\n        ";
                }
            }
        }
        return $fields;
    }

    protected function getRelationForColumn(string $column, array $relations): ?string
    {
        if (Str::endsWith($column, '_id')) {
            $potentialRelation = Str::before($column, '_id');
            return array_key_exists($potentialRelation, $relations) ? $potentialRelation : null;
        }
        return null;
    }

    protected function getValidationRules(string $type, string $column): string
    {
        $rules = [];
        
        switch ($type) {
            case 'string':
                $rules[] = 'string';
                $rules[] = 'max:255';
                if (Str::contains($column, 'email')) $rules[] = 'email';
                break;
            case 'integer': $rules[] = 'integer'; break;
            case 'boolean': $rules[] = 'boolean'; break;
            case 'date': case 'datetime': $rules[] = 'date'; break;
            default: $rules[] = 'string';
        }
        
        if ($type !== 'boolean') $rules[] = 'required';
        
        return implode('|', $rules);
    }

    protected function getUpdateValidationRules(string $type, string $column, string $modelName): string
    {
        $rules = $this->getValidationRules($type, $column);
        
        if ($type === 'string') {
            $model = app("App\\Models\\{$modelName}");
            $table = $model->getTable();
            
            if (Str::contains($column, 'email')) {
                $rules .= "|unique:{$table},{$column},:id";
            }
            
            $indexes = Schema::getConnection()
                ->getDoctrineSchemaManager()
                ->listTableIndexes($table);
                
            foreach ($indexes as $index) {
                if ($index->isUnique() && in_array($column, $index->getColumns())) {
                    $rules .= "|unique:{$table},{$column},:id";
                    break;
                }
            }
        }
        
        return $rules;
    }

    protected function getFormFieldType(string $dbType): string
    {
        return match($dbType) {
            'string' => 'text',
            'integer' => 'number',
            'boolean' => 'checkbox',
            'text' => 'textarea',
            'date' => 'date',
            'datetime' => 'datetime-local',
            'password' => 'password',
            'email' => 'email',
            default => 'text'
        };
    }

    protected function createVueComponents($resourceName, $panel, $modelName)
    {
        $panelLower = Str::lower($panel);
        $basePath = resource_path("js/pages/{$panelLower}/Resources/{$resourceName}Resource");
        
        // Récupérer les colonnes du modèle
        $model = app("App\\Models\\{$modelName}");
        $table = $model->getTable();
        $columns = Schema::getColumnListing($table);
        $fillable = $model->getFillable();
        $columns = array_diff($columns, ['id', 'created_at', 'updated_at', 'deleted_at']);
        
        // Détection des relations
        $relations = $this->detectModelRelations($modelName);
        
        foreach (['index', 'create', 'edit', 'show'] as $view) {
            $stubPath = __DIR__.'/stubs/'.$view.'.vue.stub';
            
            if ($this->filesystem->exists($stubPath)) {
                $stub = $this->filesystem->get($stubPath);
                $modelVar = Str::camel($modelName);

                // Générer l'interface TypeScript pour index.vue
                if ($view === 'index') {
                    $interfaceFields = '';
                    foreach ($columns as $column) {
                        $type = $this->getTypescriptType(Schema::getColumnType($table, $column));
                        $interfaceFields .= "  {$column}: {$type};\n";
                    }
                    
                    $stub = str_replace(
                        [
                            '{{modelName}}',
                            '{{interfaceFields}}',
                            '{{panel}}',
                            '{{label}}'
                        ],
                        [
                            $modelName,
                            trim($interfaceFields),
                            $panel,
                            Str::headline($resourceName)
                        ],
                        $stub
                    );
                } 
                
                if ($view === 'edit' || $view === 'create') {
                    // Générer les valeurs initiales du formulaire
                    $formInitialValues = '';
                    foreach ($columns as $column) {
                        if (in_array($column, $fillable)) {
                            $formInitialValues .= "'{$column}': ".($view === 'edit' ? "props.{$modelVar}.{$column}" : "''").",\n      ";
                        }
                    }
                    
                    // Générer les champs du formulaire
                    $formFields = '';
                    foreach ($columns as $column) {
                        if (in_array($column, $fillable)) {
                            $relationName = $this->getRelationForColumn($column, $relations);
                            $label = Str::headline($column);
                            
                            if ($relationName) {
                                $formFields .= <<<HTML
        <div>
          <label for="{$column}" class="block font-medium">{$label}</label>
          <select v-model="form.{$column}" id="{$column}" required>
            <option value="">Select {$label}</option>
            <option v-for="(name, id) in {$relationName}Options" :key="id" :value="id">{{ name }}</option>
          </select>
        </div>

HTML;
                            } else {
                                $type = $this->getFormFieldType(Schema::getColumnType($table, $column));
                                $formFields .= <<<HTML
        <div>
          <label for="{$column}" class="block font-medium">{$label}</label>
          <Input v-model="form.{$column}" type="{$type}" id="{$column}" required />
        </div>

HTML;
                            }
                        }
                    }
                    
                    $stub = str_replace(
                        [
                            '{{modelName}}',
                            '{{formInitialValues}}',
                            '{{formFields}}',
                            '{{panel}}',
                            '{{label}}',
                            '{{relationsComputed}}' => $this->generateVueRelationsComputed($relations),
                            '{{relationsLoad}}' => $this->generateVueRelationsLoad($relations),
                        ],
                        [
                            $modelName,
                            trim($formInitialValues),
                            trim($formFields),
                            $panel,
                            Str::headline($resourceName),
                            $this->generateVueRelationsComputed($relations),
                            $this->generateVueRelationsLoad($relations),
                        ],
                        $stub
                    );
                }
                
                $this->filesystem->put("{$basePath}/{$view}.vue", $stub);
            }
        }
    }
    
    protected function generateVueRelationsComputed(array $relations): string
    {
        if (empty($relations)) return '';
        
        $computed = '';
        foreach (array_keys($relations) as $relationName) {
            $computed .= "{$relationName}Options() {\n      return this.relationOptions.{$relationName} || {};\n    },\n    ";
        }
        return $computed;
    }
    
    protected function generateVueRelationsLoad(array $relations): string
    {
        if (empty($relations)) return '';
        
        $load = '';
        foreach (array_keys($relations) as $relationName) {
            $load .= "{$relationName}: await ResourceApi.relations('{$relationName}'),\n      ";
        }
        return $load;
    }
    
    protected function getTypescriptType(string $dbType): string
    {
        return match($dbType) {
            'integer', 'bigint', 'float', 'double', 'decimal' => 'number',
            'boolean' => 'boolean',
            'json' => 'any',
            'date', 'datetime', 'timestamp' => 'string | Date',
            default => 'string'
        };
    }
    
    protected function updatePanelConfig($resourceName, $panel)
    {
        $providerPath = app_path('Providers/PanelServiceProvider.php');
        
        if ($this->filesystem->exists($providerPath)) {
            $content = $this->filesystem->get($providerPath);
            $resourceClass = "App\\Resources\\{$panel}\\{$resourceName}Resource::class";
            
            if (!str_contains($content, $resourceClass)) {
                $content = preg_replace(
                    "/protected static array \$panels = \[([^\]]*)\]/",
                    "protected static array \$panels = [\n            '{$panel}' => [\n                'resources' => [\n                    {$resourceClass},\n                ],\n            ],\n        ]",
                    $content
                );
                $this->filesystem->put($providerPath, $content);
            }
        }
    }
}
